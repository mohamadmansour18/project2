<?php

namespace App\Enums;

use App\Traits\EnumToArray;

enum ProfileStudentSpeciality: string
{
    use EnumToArray;
    case Backend = 'backend';
    case Front_Mobile = 'front_mobile';

    case Front_Web = 'front_Web' ;
}
