<?php

namespace App\Enums;

use App\Traits\EnumToArray;

enum GroupType: string
{
    use EnumToArray;
    case Public = 'public' ;
    case Private = 'private' ;
}
