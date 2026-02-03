<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class Helper
{
    /**
     * Upload file using Laravel Storage (public disk)
     */
    public static function fileUpload($file, string $folder): ?string
    {
        if (!$file || !$file->isValid()) {
            return null;
        }

        // Generate unique filename
        $fileName = time() . '-' . Str::random(8) . '.' . $file->getClientOriginalExtension();

        // Store file in: storage/app/public/{folder}
        $path = $file->storeAs(
            'uploads/' . trim($folder, '/'),
            $fileName,
            'public'
        );

        // Return path usable in frontend
        return $path; // ex: uploads/avatars/abc123.png
    }

    /**
     * Delete file from Laravel Storage (public disk)
     *
     * @param string|null $path
     * @return void
     */
    public static function fileDelete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
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
