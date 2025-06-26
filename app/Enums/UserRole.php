<?php

namespace App\Enums;

use App\Traits\EnumToArray;

enum UserRole: string
{
    use EnumToArray;
    case Student = 'student';
    case Doctor = 'doctor';
    case Admin = 'admin';

}
