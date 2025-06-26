<?php

namespace App\Enums;

use App\Traits\EnumToArray;

enum GroupMemberRole: string
{
    use EnumToArray;
    case Leader = 'leader';
    case Member = 'member';
}
