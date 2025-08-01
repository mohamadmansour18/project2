<?php

namespace App\Helpers;

class UrlHelper
{
    public static function imageUrl(?string $path): ?string
    {
        if(!$path)
        {
            return null;
        }

        return config('app.url') . '/storage/' . ltrim($path , '/');
    }
}
