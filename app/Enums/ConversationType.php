<?php

namespace App\Enums;

use App\Traits\EnumToArray;

enum ConversationType: string
{
    use EnumToArray;
    case Student_Doctor = 'student_doctor';
    case Student_Student = 'student_student';
    case Self = 'self';
}
