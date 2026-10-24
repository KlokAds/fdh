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
     * Smaller copies saved next to every upload, so phones download a photo near the size they show it.
     *
     * @var list<int>
     */
    public const VARIANT_WIDTHS = [160, 480, 960, 1440];

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
        $this->makeVariants($path);

        return $path;
    }

    /**
     * Write the smaller copies of a stored image that are still missing. Returns how many were written.
     */
    public function makeVariants(string $path): int
    {
        $disk = Storage::disk('public');
        $source = $disk->path($path);
        $size = is_file($source) ? @getimagesize($source) : false;

        if ($size === false || self::isVariant($path)) {
            return 0;
        }

        $written = 0;

        foreach (self::VARIANT_WIDTHS as $width) {
            if ($width >= $size[0] || $disk->exists(self::variantPath($path, $width))) {
                continue;
            }

            $copy = ImageManager::usingDriver(Driver::class)->decodePath($source)->scaleDown(width: $width);
            $disk->put(self::variantPath($path, $width), (string) $copy->encode(new WebpEncoder(quality: 76, strip: true)));
            $written++;
        }

        return $written;
    }

    /**
     * Write the missing copies for every photo in the media library folders.
     */
    public function makeAllVariants(): int
    {
        $written = 0;

        foreach (array_keys(MediaLibrary::FOLDERS) as $folder) {
            foreach (Storage::disk('public')->allFiles($folder) as $path) {
                if (str_ends_with($path, '.webp') && ! self::isVariant($path)) {
                    $written += $this->makeVariants($path);
                }
            }
        }

        return $written;
    }

    /**
     * The srcset for a stored image: its smaller copies plus the original, or null when it has none.
     */
    public static function srcset(?string $path): ?string
    {
        $disk = Storage::disk('public');

        if ($path === null || $path === '' || str_contains($path, '..') || ! $disk->exists($path)) {
            return null;
        }

        $sources = [];

        foreach (self::VARIANT_WIDTHS as $width) {
            if ($disk->exists(self::variantPath($path, $width))) {
                $sources[] = $disk->url(self::variantPath($path, $width)).' '.$width.'w';
            }
        }

        $size = @getimagesize($disk->path($path));

        if ($sources === [] || $size === false) {
            return null;
        }

        $sources[] = $disk->url($path).' '.$size[0].'w';

        return implode(', ', $sources);
    }

    public static function variantPath(string $path, int $width): string
    {
        return preg_replace('/\.webp$/', '', $path).'-'.$width.'.webp';
    }

    public static function isVariant(string $path): bool
    {
        return preg_match('/-('.implode('|', self::VARIANT_WIDTHS).')\.webp$/', $path) === 1;
    }

    public function delete(?string $path): void
    {
        if ($path !== null && $path !== '' && ! str_contains($path, '..')) {
            Storage::disk('public')->delete([$path, ...array_map(fn (int $width): string => self::variantPath($path, $width), self::VARIANT_WIDTHS)]);
        }
    }
}
