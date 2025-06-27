<?php

namespace App\Services;

use App\Enums\OtpCodePurpose;
use App\Enums\UserRole;
use App\Exceptions\RegistrationException;
use App\Jobs\SendOtpCodeJob;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DoctorService
{
    public function __construct()
    {

    }

    public function registerDoctor(array $data): void
    {
        $user = User::query()->where('email' , $data['email'])->where('role' , UserRole::Admin->value)->first();

        if(!$user || $user->password)
        {
            throw new RegistrationException('لا يمكنك انشاء هذا الحساب !' , 'هذا البريد مستخدم مسبقا أو غير مصرح به اساسا .');
        }

        DB::transaction(function () use ($user , $data){

          $user->update([
             'password' => Hash::make($data['password'])
          ]);

          $user->Profile()->updateOrCreate([] , [
              'governorate' => $data['governorate']
          ]);
        });

        $otp = OtpCode::createOtpFor($user->id , OtpCodePurpose::Verification->value);

        SendOtpCodeJob::dispatch($user->email , $otp->otp_code , $user->name);
    }

    public function verifyOtp(array $data): void
    {
        $user = User::query()->where('email' , $data['email'])->first();

        if(!$user)
        {
            throw new RegistrationException('المستخدم غير موجود !'  , 'لم يتم العثور على مستخدم بهذا البريد');
        }

        $otp = OtpCode::query()->where('user_id' , $user->id)
                               ->where('otp_code' , $data['otp_code'])
                               ->where('expires_at' , '>' , now())
                               ->where('is_used' , false)
                               ->where('purpose' , OtpCodePurpose::Verification->value)
                               ->first();

        if(!$otp)
        {
            throw new RegistrationException('رمز غير صالح !' , 'عذرا الرمز الذي قمت باستخدامه غير صالح ، يرجى ادخال الرمز الصحيح');
        }

        DB::transaction(function () use ($user , $otp){
            $user->update([
                'email_verified_at' => now()
            ]);

            $otp->update([
                'is_used' => true
            ]);
        });
    }

    public function resendOtp(array $data): void
    {
        $user = User::query()->where('email' , $data['email'])->first();

        if(!$user)
        {
            throw new RegistrationException('المستخدم غير موجود !' , 'لايوجد مستخدم مرتبط بالبريد المدخل لايمكننا ارسال الرمز');
        }

        if($user->email_verified_at)
        {
            throw new RegistrationException('البريد المؤكد !' , 'عزيزي المستخدم لقد تم تأكيد بريدك الالكتروني مسبقا');
        }

        $otp = OtpCode::createOtpFor($user->id , OtpCodePurpose::Verification->value);

        SendOtpCodeJob::dispatch($user->email , $user->name , $otp->otp_code);
    }
}
