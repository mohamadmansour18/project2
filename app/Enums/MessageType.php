<?php

namespace App\Enums;

use App\Traits\EnumToArray;

enum MessageType: string
{
    use EnumToArray;
    case Text = 'text';
    case Image = 'image';
    case File = 'file';
    case Auto_Reply = 'auto_reply';
}
