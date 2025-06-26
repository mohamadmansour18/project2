<?php

namespace App\Enums;

use App\Traits\EnumToArray;

enum Days: string
{
    use EnumToArray;
    case Sunday    = 'sunday';
    case Monday    = 'monday';
    case Tuesday   = 'tuesday';
    case Wednesday = 'wednesday';
    case Thursday  = 'thursday';
    case Friday    = 'friday';
    case Saturday  = 'saturday';
}
