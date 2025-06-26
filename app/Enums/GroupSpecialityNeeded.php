<?php

namespace App\Enums;

use App\Traits\EnumToArray;

enum GroupSpecialityNeeded: string
{
    use EnumToArray;
    case Backend = 'Backend';
    case Front_Web = 'front_web';
    case Front_Mobile = 'front_mobile';
    case Ui_Ux = 'ui_ux';
}
