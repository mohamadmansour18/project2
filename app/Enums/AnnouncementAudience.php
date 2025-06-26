<?php

namespace App\Enums;



use App\Traits\EnumToArray;

enum AnnouncementAudience: string
{
    use EnumToArray;
    case All = 'all';
    case Professors = 'professors' ;
}
