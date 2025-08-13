<?php

namespace App\Enums;

use App\Traits\EnumToArray;

enum JoinRequestStatus: string
{
    use EnumToArray;
    case PendingHead = 'pending_head';
    case PendingLeader ='pending_leader';
    case Pending = 'pending' ;
    case Accepted = 'accepted' ;
    case Rejected = 'rejected' ;
    case Cancelled = 'cancelled';
}
