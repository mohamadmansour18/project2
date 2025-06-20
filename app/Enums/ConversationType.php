<?php

namespace App\Enums;

enum ConversationType: string
{
    case Student_Doctor = 'student_doctor';
    case Student_Student = 'student_student';
    case Self = 'self';
}
