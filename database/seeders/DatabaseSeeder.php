<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Admin accounts are never seeded: create them with `php artisan fdh:create-admin`.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            ContentSeeder::class,
        ]);
    }
}
