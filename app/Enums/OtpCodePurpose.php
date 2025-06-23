<?php

namespace App\Enums;

enum OtpCodePurpose: string
{
    case Verification = 'email_verification';
    case Reset = 'password_reset';
}
