<?php

use App\Support\ImageStore;
use App\Support\MediaLibrary;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;

/**
 * Remakes the resized copies at the lower variant quality, so phones download less for the same picture.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        @set_time_limit(0);
        $disk = Storage::disk('public');

        foreach (array_keys(MediaLibrary::FOLDERS) as $folder) {
            $disk->delete(array_values(array_filter($disk->allFiles($folder), fn (string $path): bool => ImageStore::isVariant($path))));
        }

        (new ImageStore)->makeAllVariants();
    }

    public function down(): void
    {
        //
    }
};
