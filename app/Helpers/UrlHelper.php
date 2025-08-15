<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class UrlHelper
{
    public static function imageUrl(?string $path): ?string
    {
        if(!$path)
        {
            return null;
        }

        //asset : generate APP_URL & Storage::url generate rest of url : /storage/app/public/{image_path}
        return asset(Storage::url($path));
    }
}
