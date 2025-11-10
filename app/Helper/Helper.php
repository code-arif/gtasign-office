<?php

namespace App\Helper;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Helper
{
    public static function uploadImage($file, $folder)
    {

        if (!$file->isValid()) {
            return null;
        }

        $imageName = time() . '-' . Str::random(5) . '.' . $file->extension(); // Unique name
        $path = public_path('uploads/' . $folder);

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $file->move($path, $imageName);
        return 'uploads/' . $folder . '/' . $imageName;
    }

    public static function fileUpload($file, string $folder, string $name): ?string
    {
        if (!$file->isValid()) {
            return null;
        }

        $imageName = Str::slug($name) . '.' . $file->extension();
        $path      = public_path('uploads/' . $folder);
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $file->move($path, $imageName);
        return 'uploads/' . $folder . '/' . $imageName;
    }

    public static function fileDelete(string $path): void
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }



    public static function deleteImage($imageUrl)
    {
        if (!$imageUrl) {
            return false;
        }
        $filePath = public_path($imageUrl);
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    // public static function deleteImage(string $path)
    // {
    //     if (file_exists($path)) {
    //         unlink($path);
    //     }
    // }


    public static function deleteImages($imageUrls)
    {
        if (is_array($imageUrls)) {
            foreach ($imageUrls as $imageUrl) {
                $baseUrl = url('/');
                $relativePath = str_replace($baseUrl . '/', '', $imageUrl);
                $fullPath = public_path($relativePath);


                if (file_exists($fullPath) && is_file($fullPath)) {

                    if (!unlink($fullPath)) {
                        return false;
                    }
                }
            }
            return true;
        }

        return false;
    }


    // calculate age from date of birth
    public static function calculateAge($dateOfBirth)
    {
        if (!$dateOfBirth) {
            return null;
        }

        $dob = \Carbon\Carbon::parse($dateOfBirth);
        $now = \Carbon\Carbon::now();

        return (int) $dob->diffInYears($now);
    }
}
