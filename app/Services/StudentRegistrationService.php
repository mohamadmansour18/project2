<?php

namespace App\Services;

use App\Enums\OtpCodePurpose;
use App\Enums\UserRole;
use App\Events\StudentRegistered;
use App\Exceptions\RegistrationException;
use App\Jobs\SendOtpCodeJob;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentRegistrationService
{
    public function __construct()
    {
        //
    }

    public function register(array $data): void
    {
        $user = User::query()
            ->where('role' , UserRole::Student->value)
            ->first();

        if (!$user || $user->password) {
            throw new RegistrationException('لا يمكن إتمام التسجيل.', 'الرقم الجامعي غير موجود أو الاسم غير مسجل.');
        }

        $emailUsedByAnother = User::query()
            ->where('email', $data['email'])
            ->where('id', '!=', $user->id)
            ->exists();

        if ($emailUsedByAnother) {
            throw new RegistrationException('لا يمكن إتمام التسجيل.', 'البريد الإلكتروني مستخدم مسبقًا.');
        }

        DB::transaction(function () use ($user, $data) {
            $user->update([
                'email'     => $data['email'],
                'password'  => Hash::make($data['password']),
            ]);
        });

        $otp = OtpCode::createOtpFor($user->id , OtpCodePurpose::Verification->value);

        SendOtpCodeJob::dispatch($user->email , $otp->otp_code , $user->name);


    }
}
