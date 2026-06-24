<?php

namespace App\Helpers;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageSanitizer
{
    /**
     * Re-encodes the image to strip EXIF data and embedded malicious scripts (Polyglot).
     *
     * @param  string  $fullPath  Absolute path to the saved image file
     * @param  string  $extension  The file extension
     */
    public static function sanitize(string $fullPath, string $extension): void
    {
        $extension = strtolower($extension);

        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            try {
                $manager = new ImageManager(Driver::class);
                $image = $manager->decodePath($fullPath);

                // Saving it back re-encodes the image and strips out non-image data
                $image->save($fullPath);
            } catch (\Exception $e) {
                // If the image cannot be read, it might be a corrupted or completely fake image.
                // We could delete it, but SafeFile already validates the basic structure.
                // We'll throw an exception or delete it here.
                @unlink($fullPath);
                throw new \Exception('File gambar tidak valid atau rusak.');
            }
        }
    }
}
