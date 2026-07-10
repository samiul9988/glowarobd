<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Http\UploadedFile;

class ImageUploader
{
    private static function getTypes()
    {
        return [
            "jpg" => "image",
            "jpeg" => "image",
            "png" => "image",
            "svg" => "image",
            "webp" => "image",
            "gif" => "image",
            "mp4" => "video",
            "mpg" => "video",
            "mpeg" => "video",
            "webm" => "video",
            "ogg" => "video",
            "avi" => "video",
            "mov" => "video",
            "flv" => "video",
            "swf" => "video",
            "mkv" => "video",
            "wmv" => "video",
            "wma" => "audio",
            "aac" => "audio",
            "wav" => "audio",
            "mp3" => "audio",
            "zip" => "archive",
            "rar" => "archive",
            "7z" => "archive",
            "doc" => "document",
            "txt" => "document",
            "docx" => "document",
            "pdf" => "document",
            "csv" => "document",
            "xml" => "document",
            "ods" => "document",
            "xlr" => "document",
            "xls" => "document",
            "xlsx" => "document",
        ];
    }

    public static function upload($file, $dir = 'uploads/all')
    {
        if ($file instanceof UploadedFile) {
            $extension = $file->extension();
        } elseif (self::isBase64Image($file)) {
            $extension = self::getBase64Extension($file);
        } else {
            return ['error' => true, 'message' => 'Invalid file format.'];
        }

        $fileName = self::generateFileName($extension);
    }

    private static function generateFileName(string $extension, ?string $guard = null): string
    {
        $auth = auth()->guard($guard);

        if ($auth->check()) {
            $user = $auth->user();

            // Safe & clean username slug
            $username = Str::slug($user->name, '_') ?: 'unknown_user'; // e.g. "John Doe" → "john_doe"

            return time() . "_{$username}.{$extension}";
        }

        // Fallback for unknown user
        return 'unknown_user_' . Str::lower(Str::random(25)) . '.' . $extension;
    }

    private static function isBase64Image($file): bool
    {
        if (!is_string($file) || empty($file)) {
            return false;
        }

        // Check if it contains base64 header (optional)
        if (preg_match('/^data:image\/(\w+);base64,/', $file, $match)) {
            $file = substr($file, strpos($file, ',') + 1);
        }

        // Base64 validation
        if (!base64_decode($file, true)) {
            return false;
        }

        // Check if decoded string is an actual image
        $imageData = base64_decode($file);

        // getimagesizefromstring returns false if not an image
        if (!@getimagesizefromstring($imageData)) {
            return false;
        }

        return true;
    }

    private static function getBase64Extension($str)
    {
        preg_match('/^data:image\/(\w+);base64,/', $str, $matches);
        return strtolower($matches[1] ?? 'png');
    }

    private static function isImage($extension)
    {
        return self::getTypes()[$extension] === 'image';
    }

    private static function isVideo($extension)
    {
        return self::getTypes()[$extension] === 'video';
    }

    private static function isOptimizationDisabled()
    {
        return (bool) get_setting('disable_image_optimization', 0);
    }
}
