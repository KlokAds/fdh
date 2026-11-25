<?php

namespace Database\Seeders;

use App\Support\SampleContent;
use Illuminate\Database\Seeder;

class SampleContentSeeder extends Seeder
{
    /**
     * Placeholder photos and sample projects so a fresh site looks complete; removable from the admin dashboard.
     */
    public function run(SampleContent $samples): void
    {
        $samples->install();
    }
}
