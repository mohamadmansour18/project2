<?php

namespace App\Enums;

enum UserRole: string
{
    case Student = 'student';
    case Doctor = 'doctor';
    case Admin = 'admin';

}
