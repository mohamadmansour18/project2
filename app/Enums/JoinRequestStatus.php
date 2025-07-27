<?php

namespace App\Enums;

use App\Traits\EnumToArray;

enum JoinRequestStatus: string
{
    use EnumToArray;
    case Pending = 'pending' ;
    case Accepted = 'accepted' ;
    case Rejected = 'rejected' ;
    case Cancelled = 'cancelled';
}
