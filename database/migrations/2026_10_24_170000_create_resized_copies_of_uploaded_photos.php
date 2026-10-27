<?php

use App\Support\ImageStore;
use Illuminate\Database\Migrations\Migration;

/**
 * Photos uploaded before resized copies existed get them now, so phones stop downloading full-size files.
 * New uploads make their own copies; deleting a photo removes them.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        @set_time_limit(0);
        (new ImageStore)->makeAllVariants();
    }

    public function down(): void
    {
        //
    }
};
