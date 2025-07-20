<?php

namespace App\Services;

use App\Enums\OtpCodePurpose;
use App\Enums\UserRole;
use App\Exceptions\LoginException;
use App\Exceptions\RegistrationException;
use App\Exceptions\ResetPasswordException;
use App\Jobs\SendOtpCodeJob;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AdminRegistrationService
{
    public function __construct()
    {

    }

    public function login(array $AdminLoginData): array
    {
        $user = User::query()->where('email' , $AdminLoginData['email'])->where('role' , UserRole::Admin->value)->first();

        if(!$user || !Hash::check($AdminLoginData['password'] , $user->password))
        {
            throw new LoginException('فشل تسجيل الدخول !' , 'البريد الإلكتروني أو كلمة المرور غير صحيحة');
        }

        $token = JWTAuth::fromUser($user);

        return [
            'token' => $token ,
            'name' =>  'الدكتور ' . $user->name  ,
            'profile_image' => $user->profile->profile_image
        ];
    }

    ///////////////////////////////////////////////////////////////////

    public function sendPasswordResetOtp(string $email): void
    {
        $user = User::query()->where('email' , $email)->where('role' , UserRole::Admin->value)->first();

        if(!$user || !$user->password)
        {
            throw new ResetPasswordException('غير مصرح به !' , 'هذا البريد غير مرتبط بحساب لمشرف في النظام');
        }

        $otp = OtpCode::createOtpFor($user->id , OtpCodePurpose::Reset->value);

        SendOtpCodeJob::dispatch($email , $otp->otp_code , $user->name , $otp->purpose);
    }


    public function verifyPasswordResetOtp(array $data): void
    {
        $user = User::query()->where('email' , $data['email'])->where('role' , UserRole::Admin->value)->first();

        if(!$user || !$user->password)
        {
            throw new ResetPasswordException('غير مصرح به !' , 'هذا البريد غير مرتبط بحساب لمشرف في النظام');
        }

        $latestOtp = OtpCode::query()
            ->where('user_id' , $user->id)
            ->where('purpose' , OtpCodePurpose::Reset->value)
            ->orderByDesc('created_at')
            ->first();

        if(!$latestOtp || $latestOtp->otp_code != $data['otp_code'] || $latestOtp->is_used || $latestOtp->expires_at < now())
        {
            throw new RegistrationException('رمز غير صالح !' , 'عذرا الرمز الذي قمت باستخدامه غير صالح ، يرجى ادخال الرمز الصحيح');
        }

        $latestOtp->update([
            'is_used' => true
        ]);
    }

    public function resetPassword(array $data): void
    {
        $user =  User::query()->where('email' , $data['email'])->where('role' , UserRole::Admin->value)->first();

        if(!$user || !$user->password)
        {
            throw new ResetPasswordException('غير مصرح به !' , 'هذا البريد غير مرتبط بحساب لمشرف في النظام');
        }

        if(Hash::check($data['password'] , $user->password))
        {
            throw new ResetPasswordException('كلمة مرور غير صالحة !' , 'يرجى اختيار كلمة مرور مختلفة عن الحالية');
        }

        $user->update([
            'password' => Hash::make($data['password'])
        ]);
    }

    public function resendPasswordResetOtp(string $email): void
    {
        $user = User::query()->where('email' , $email)->where('role' , UserRole::Admin->value)->first();

        if(!$user || !$user->password)
        {
            throw new RegistrationException('المستخدم غير موجود !' , 'لايوجد مستخدم مرتبط بالبريد المدخل لايمكننا ارسال الرمز');
        }

        $otp = OtpCode::createOtpFor($user->id , OtpCodePurpose::Reset->value);

        SendOtpCodeJob::dispatch($user->email , $otp->otp_code , $user->name , $otp->purpose);
    }



}
