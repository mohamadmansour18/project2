<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ImageService
{
    public function __construct()
    {
        //
    }

    /**
     * Stores an uploaded image file with a custom name format:
     * {slugified base name}_{timestamp}.{extension}
     *
     * @param UploadedFile $file    The uploaded image file.
     * @param string       $folder  The folder to store the image in (inside 'public' disk).
     * @param string       $baseName The base name used for the file name.
     *
     * @return string The public URL path to the stored image (e.g. "storage/group_images/file.jpg").
     */
    public function storeImageWithCustomName(UploadedFile $file, string $folder, string $baseName): string
    {
        $extension = $file->getClientOriginalExtension();
        $baseNameSlug = Str::slug($baseName);
        $dateString = date('Ymd_His');
        $fileName = $baseNameSlug . '_' . $dateString . '.' . $extension;

        $path = $file->storeAs($folder, $fileName, 'public');

        return 'storage/' . $path;
    }

    /**
     * Generates a QR code image and stores it with a custom name format:
     * {slugified base name}_{timestamp}.png
     *
     * @param string $baseName The base name used for the QR code file name.
     *
     * @return string The public URL path to the stored QR code (e.g. "storage/qrcodes/file.png").
     */
    public function generateAndStoreQrCode(string $baseName): string
    {
        $qrCodeString = (string) Str::uuid();
        $baseNameSlug = Str::slug($baseName);
        $dateString = date('Ymd_His');
        $qrCodeFileName = $baseNameSlug . '_' . $dateString . '.png';
        $qrImageStoragePath = 'qrcodes/' . $qrCodeFileName;

        Storage::disk('public')->put($qrImageStoragePath, QrCode::format('png')->size(300)->generate($qrCodeString));

        return 'storage/' . $qrImageStoragePath;
    }

    /**
     * Generate full URL for a stored public path
     * @param string $relativePath e.g. 'storage/group_images/example.png'
     * @return string full URL e.g. http://localhost/storage/group_images/example.png
     */
    public function getFullUrl(string $relativePath): string
    {
        return asset($relativePath);
    }
}
