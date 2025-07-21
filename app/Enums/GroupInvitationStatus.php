<?php

namespace App\Enums;

use App\Traits\EnumToArray;

enum GroupInvitationStatus: string
{
    use EnumToArray;
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
}
