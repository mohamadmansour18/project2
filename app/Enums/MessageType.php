<?php

namespace App\Enums;

enum MessageType: string
{
    case Text = 'text';
    case Image = 'image';
    case File = 'file';
    case Auto_Reply = 'auto_reply';
}
