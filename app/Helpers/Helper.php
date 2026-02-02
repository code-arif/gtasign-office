<?php

namespace App\Helpers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class Helper
{
    /**
     * Upload file
     */
    public static function fileUpload($file, $folder): ?string
    {
        if (!$file->isValid()) {
            return null;
        }

        $imageName = time() . '-' . Str::random(5) . '.' . $file->getClientOriginalExtension();
        $path      = public_path('uploads/' . $folder);
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $file->move($path, $imageName);
        return 'uploads/' . $folder . '/' . $imageName;
    }

    /**
     * Delete file
     */
    public static function fileDelete(string $path): void
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * Generate a random alphanumeric string.
     */
    public static function randomAlphaNum($length = 8)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Generate slug for user profile
     */
    public static function generateSlug(string $firstName): string
    {
        return strtolower($firstName) . self::randomAlphaNum(8);
    }

    /**
     * Generate username for user profile
     */
    public static function generateUsername(string $firstName): string
    {
        return '@' . strtolower($firstName) . self::randomAlphaNum(8);
    }
}
