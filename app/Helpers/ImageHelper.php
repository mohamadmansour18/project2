<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ImageHelper
{
    public static function storeImageWithCustomName(UploadedFile $file, string $folder, string $baseName): string
    {
        $extension = $file->getClientOriginalExtension();
        $baseNameSlug = Str::slug($baseName);
        $dateString = date('Ymd_His');
        $fileName = $baseNameSlug . '_' . $dateString . '.' . $extension;

        $path = $file->storeAs($folder, $fileName, 'public');

        return $path;
    }

    public static function generateAndStoreQrCode(string $baseName): string
    {
        $qrCodeString = (string) Str::uuid();
        $baseNameSlug = Str::slug($baseName);
        $dateString = date('Ymd_His');
        $qrCodeFileName = $baseNameSlug . '_' . $dateString . '.png';
        $qrImageStoragePath = 'qrcodes/' . $qrCodeFileName;

        Storage::disk('public')->put(
            $qrImageStoragePath,
            QrCode::format('png')->size(300)->generate($qrCodeString)
        );

        return $qrImageStoragePath;
    }

}
