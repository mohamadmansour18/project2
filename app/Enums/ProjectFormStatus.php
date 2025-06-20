<?php

namespace App\Enums;

enum ProjectFormStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
