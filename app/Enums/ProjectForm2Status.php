<?php

namespace App\Enums;

use App\Traits\EnumToArray;

enum ProjectForm2Status: string
{
    use EnumToArray;
    case Pending = 'pending';
    case Signed = 'signed' ;
}
