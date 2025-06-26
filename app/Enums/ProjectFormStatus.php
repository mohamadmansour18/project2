<?php

namespace App\Enums;

use App\Traits\EnumToArray;

enum ProjectFormStatus: string
{
    use EnumToArray;
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
