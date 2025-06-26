<?php

namespace App\Traits;

/**
 * @mixin \BackedEnum
 */
trait EnumToArray
{
    public static function convertEnumToArray(): array
    {
        return array_column(static::cases() , 'value');
    }
}
