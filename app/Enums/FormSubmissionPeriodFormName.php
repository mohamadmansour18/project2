<?php

namespace App\Enums;

use App\Traits\EnumToArray;

enum FormSubmissionPeriodFormName: string
{
    use EnumToArray;
    case Form1 = 'form1';
    case Form2 = 'form2';
}
