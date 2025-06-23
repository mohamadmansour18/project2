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

class SupervisorService
{
    public function __construct()
    {
        //
    }

    public function registerSupervisor(array $data): void
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
}
