<?php

namespace App\Enums;

use App\Traits\EnumToArray;

enum MessageStatus : string
{
    use EnumToArray ;
    case Sent = 'sent' ;
    case Delivered = 'delivered';
    case Read = 'read';
}
