<?php

namespace App\Enums;

use App\Traits\EnumToArray;

enum AnnouncementType: string
{
    use EnumToArray;
    case Image = 'image';
    case File = 'file';
}
