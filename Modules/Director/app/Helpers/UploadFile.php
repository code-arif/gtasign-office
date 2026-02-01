<?php

namespace Modules\Director\Helpers;

use Illuminate\Http\UploadedFile;

class UploadFile
{
    /**
     * Upload an image to public folder
     *
     * @param UploadedFile $file
     * @param string $folder
     * @return string
     */
    public static function uploadFiles(UploadedFile $file, $folder = 'uploads/director')
    {
        $path = public_path($folder);

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
        $filename = time() . '_' . $file->getClientOriginalName();

        $file->move($path, $filename);

        return $folder . '/' . $filename;
    }

    /**
     * Delete an image from public folder
     *
     * @param string $filePath
     * @return bool
     */
    public static function deleteImage($filePath)
    {
        $fullPath = public_path($filePath);

        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false; 
    }
}
