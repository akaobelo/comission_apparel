<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizer
{
    /**
     * Resizes and converts an uploaded image to WebP format if GD is available.
     * Otherwise, falls back to standard file storage.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string $disk
     * @param int $maxWidth
     * @param int $quality
     * @return string The stored path relative to the disk root
     */
    public static function optimize(UploadedFile $file, string $directory, string $disk = 'public', int $maxWidth = 1200, int $quality = 80): string
    {
        // If GD extension is not loaded, fall back to default store
        if (!extension_loaded('gd')) {
            return $file->store($directory, $disk);
        }

        $imageInfo = @getimagesize($file->getRealPath());
        if ($imageInfo === false) {
            return $file->store($directory, $disk);
        }

        $mime = $imageInfo['mime'];
        $width = $imageInfo[0];
        $height = $imageInfo[1];

        // Create image resource based on mime type
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $src = @imagecreatefromjpeg($file->getRealPath());
                break;
            case 'image/png':
                $src = @imagecreatefrompng($file->getRealPath());
                if ($src) {
                    imagealphablending($src, false);
                    imagesavealpha($src, true);
                }
                break;
            case 'image/gif':
                $src = @imagecreatefromgif($file->getRealPath());
                break;
            case 'image/webp':
                $src = @imagecreatefromwebp($file->getRealPath());
                break;
            default:
                // Unsupported type, fall back to default upload
                return $file->store($directory, $disk);
        }

        if (!$src) {
            return $file->store($directory, $disk);
        }

        // Calculate aspect ratio and new dimensions
        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = (int) (($height / $width) * $maxWidth);
            
            $dst = imagecreatetruecolor($newWidth, $newHeight);
            if ($dst) {
                // Preserve transparency in resized image
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($src);
                $src = $dst;
            }
        }

        // Generate filename
        $filename = Str::random(40) . '.webp';
        $tempPath = tempnam(sys_get_temp_dir(), 'optimized_img');

        // Convert to WebP and save temporarily
        if (@imagewebp($src, $tempPath, $quality)) {
            imagedestroy($src);

            // Put the optimized image in Laravel storage
            $storedPath = $directory . '/' . $filename;
            Storage::disk($disk)->put($storedPath, fopen($tempPath, 'r'));
            
            // Clean up temporary file
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }

            return $storedPath;
        }

        @imagedestroy($src);
        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }

        return $file->store($directory, $disk);
    }
}
