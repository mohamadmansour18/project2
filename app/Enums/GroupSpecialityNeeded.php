<?php

namespace App\Enums;

enum GroupSpecialityNeeded: string
{
    case Backend = 'Backend';
    case Front_Web = 'front_web';
    case Front_Mobile = 'front_mobile';
    case Ui_Ux = 'ui_ux';
}
