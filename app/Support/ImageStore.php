<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class ImageStore
{
    /**
     * Validation rules for every admin image upload.
     *
     * @return list<string>
     */
    public static function rules(int $minWidth = 300, int $minHeight = 200): array
    {
        return ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120', "dimensions:min_width={$minWidth},min_height={$minHeight},max_width=10000,max_height=10000"];
    }

    /**
     * Decode the upload and re-encode it as a fresh WebP with a random name. Re-encoding strips
     * EXIF/GPS metadata and anything smuggled inside the original file, so only clean pixels are served.
     */
    public function store(UploadedFile $file, string $directory, int $maxWidth = 1920): string
    {
        return $this->storeFromPath($file->getRealPath(), $directory, $maxWidth);
    }

    /**
     * Same as store(), for an image already on disk (used for the bundled sample photos).
     */
    public function storeFromPath(string $path, string $directory, int $maxWidth = 1920): string
    {
        $image = ImageManager::usingDriver(Driver::class)
            ->decodePath($path)
            ->scaleDown(width: $maxWidth);

        $path = trim($directory, '/').'/'.Str::random(40).'.webp';
        Storage::disk('public')->put($path, (string) $image->encode(new WebpEncoder(quality: 82, strip: true)));

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path !== null && $path !== '' && ! str_contains($path, '..')) {
            Storage::disk('public')->delete($path);
        }
    }
}
