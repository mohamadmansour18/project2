<?php

namespace App\Services;

use App\Enums\OtpCodePurpose;
use App\Enums\UserRole;
use App\Exceptions\LoginException;
use App\Exceptions\RegistrationException;
use App\Exceptions\ResetPasswordException;
use App\Helpers\UrlHelper;
use App\Jobs\SendOtpCodeJob;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class StudentRegistrationService
{
    public function __construct(protected ImageService $imageService,)
    {
        //
    }

    public function register(array $data): void
    {
        $user = User::query()
            ->where('role', UserRole::Student->value)
            ->where('university_number', $data['university_id'])
            ->first();

        if (!$user || $user->password) {
            throw new RegistrationException('! لا يمكن إتمام التسجيل', 'الرقم الجامعي غير موجود أو الاسم غير مسجل.');
        }

        $emailUsedByAnother = User::query()
            ->where('email', $data['email'])
            ->where('id', '!=', $user->id)
            ->exists();

        if ($emailUsedByAnother) {
            throw new RegistrationException('! لا يمكن إتمام التسجيل', 'البريد الإلكتروني مستخدم مسبقًا.');
        }

        DB::transaction(function () use ($user, $data) {
            $user->update([
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                ['student_speciality' => $data['student_speciality']]
            );
        });

        $otp = OtpCode::createOtpFor($user->id, OtpCodePurpose::Verification->value);

        SendOtpCodeJob::dispatch($user->email, $otp->otp_code, $user->name, OtpCodePurpose::Verification->value);

    }

    public function verifyOtp(array $data): void
    {
        $user = User::query()->where('email' , $data['email'])->where('role' , UserRole::Student->value)->first();

        if(!$user)
        {
            throw new RegistrationException('! المستخدم غير موجود'  , 'لم يتم العثور على مستخدم بهذا البريد');
        }

        $latestOtp = OtpCode::query()
            ->where('user_id', $user->id)
            ->where('purpose', OtpCodePurpose::Verification->value)
            ->orderByDesc('created_at')
            ->first();

        if(!$latestOtp || $latestOtp->otp_code !== $data['otp_code'] || $latestOtp->is_used || $latestOtp->expires_at < now())
        {
            throw new RegistrationException('! رمز غير صالح' , 'عذرا الرمز الذي قمت باستخدامه غير صالح ، يرجى ادخال الرمز الصحيح');
        }

        DB::transaction(function () use ($user , $latestOtp){
            $user->update([
                'email_verified_at' => now()
            ]);

            $latestOtp->update([
                'is_used' => true
            ]);
        });
    }

    public function resendOtp(array $data): void
    {
        $user = User::query()->where('email' , $data['email'])->first();

        if(!$user)
        {
            throw new RegistrationException('! المستخدم غير موجود' , 'لايوجد مستخدم مرتبط بالبريد المدخل لايمكننا ارسال الرمز');
        }

        if($user->email_verified_at)
        {
            throw new RegistrationException('! البريد المؤكد' , 'عزيزي المستخدم لقد تم تأكيد بريدك الالكتروني مسبقا');
        }

        $otp = OtpCode::createOtpFor($user->id , OtpCodePurpose::Verification->value);

        SendOtpCodeJob::dispatch($user->email , $otp->otp_code , $user->name , $otp->purpose);
    }

    ///////////////////////////////////////////////////////////////////

    public function login(array $data): array
    {
        $user = User::query()->where('email' , $data['email'])->where('role' , UserRole::Student->value)->first();

        if(!$user->password)
        {
            throw new LoginException('! فشل تسجيل الدخول' , 'هذا الحساب غير موجود في النظام بعد قم بانشائه اولا ثم حاول مرة اخرى' , true );
        }

        if(!$user || !Hash::check($data['password'] , $user->password))
        {
            throw new LoginException('! فشل تسجيل الدخول' , 'البريد الإلكتروني أو كلمة المرور غير صحيحة' , true );
        }

        if(is_null($user->email_verified_at))
        {
            throw new LoginException('! فشل تسجيل الدخول' , 'يرجى القيام بتأكيد بريدك الالكتروني وللقيام بذلك اضغط هنا' , false );
        }

        $token = JWTAuth::fromUser($user);

        if(!empty($data['fcm_token']))
        {
            $user->fcmTokens()->updateOrCreate([
                'token' => $data['fcm_token'],
            ] , [
                'user_id' => $user->id ,
            ]);
        }

        $profileImage = $user?->profile?->profile_image;
        return [
            'token' => $token ,
            'name' => "! $user->name مرحبا "  ,
            'profile_image' => UrlHelper::imageUrl($profileImage),
            'is_in_group' => $user->groupMember()->exists(),
        ];
    }

    ///////////////////////////////////////////////////////////////////

    public function sendPasswordResetOtp(string $email): void
    {
        $user = User::query()->where('email' , $email)->where('role' , UserRole::Student->value)->first();

        if(!$user || !$user->password)
        {
            throw new ResetPasswordException('! غير مصرح به' , 'هذا البريد غير مرتبط بحساب لمشرف في النظام');
        }

        $otp = OtpCode::createOtpFor($user->id , OtpCodePurpose::Reset->value);

        SendOtpCodeJob::dispatch($email , $otp->otp_code , $user->name , $otp->purpose);
    }

    public function verifyPasswordResetOtp(array $data): void
    {
        $user = User::query()->where('email' , $data['email'])->where('role' , UserRole::Student->value)->first();

        if(!$user || !$user->password)
        {
            throw new ResetPasswordException('! غير مصرح به' , 'هذا البريد غير مرتبط بحساب لمشرف في النظام');
        }

        $latestOtp = OtpCode::query()
            ->where('user_id', $user->id)
            ->where('purpose', OtpCodePurpose::Reset->value)
            ->orderByDesc('created_at')
            ->first();

        if(!$latestOtp || $latestOtp->otp_code !== $data['otp_code'] || $latestOtp->is_used || $latestOtp->expires_at < now())
        {
            throw new RegistrationException('! رمز غير صالح' , 'عذرا الرمز الذي قمت باستخدامه غير صالح ، يرجى ادخال الرمز الصحيح');
        }

        $latestOtp->update([
            'is_used' => true
        ]);
    }

    public function resetPassword(array $data): void
    {
        $user = User::query()->where('email' , $data['email'])->where('role' , UserRole::Student->value)->first();

        if(!$user || !$user->password)
        {
            throw new ResetPasswordException('! غير مصرح به' , 'هذا البريد غير مرتبط بحساب لمشرف في النظام');
        }

        if(Hash::check($data['password'] , $user->password))
        {
            throw new ResetPasswordException('! كلمة مرور غير صالحة' , 'يرجى اختيار كلمة مرور مختلفة عن الحالية');
        }

        $user->update([
            'password' => Hash::make($data['password']) ,
        ]);
    }

    public function resendPasswordResetOtp(string $email): void
    {
        $user = User::query()->where('email' , $email)->where('role' , UserRole::Student->value)->first();

        if(!$user || !$user->password)
        {
            throw new RegistrationException('! المستخدم غير موجود' , 'لايوجد مستخدم مرتبط بالبريد المدخل لايمكننا ارسال الرمز');
        }


        $otp = OtpCode::createOtpFor($user->id , OtpCodePurpose::Reset->value);

        SendOtpCodeJob::dispatch($user->email , $otp->otp_code , $user->name , $otp->purpose);
    }




}
