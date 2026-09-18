<?php

namespace App\Actions\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait StoresImagesAsPng
{
    /**
     * Store an uploaded image converted to PNG so transparency (e.g. logos
     * with the background removed) is preserved instead of being flattened
     * onto a white background by a lossy format.
     */
    protected function storeImageAsPng(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        $source = @imagecreatefromstring(file_get_contents($file->getRealPath()));

        if ($source === false) {
            return $file->store($directory, $disk);
        }

        imagepalettetotruecolor($source);
        imagealphablending($source, false);
        imagesavealpha($source, true);

        ob_start();
        imagepng($source);
        $contents = ob_get_clean();
        imagedestroy($source);

        $path = trim($directory, '/').'/'.Str::random(40).'.png';

        Storage::disk($disk)->put($path, $contents);

        return $path;
    }
}
