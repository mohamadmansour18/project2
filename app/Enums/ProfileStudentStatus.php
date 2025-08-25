<?php

namespace App\Enums;

use App\Traits\EnumToArray;

enum ProfileStudentStatus: string
{
    use EnumToArray;
    case Fourth_Year = 'fourth_year';
    case Re_Project = 're_project';
    case Successful = 'successful';
}
