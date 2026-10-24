<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Key facts shown on a project page: progress, storeys, land and built area.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->string('status', 20)->default('completed')->after('category');
            $table->unsignedTinyInteger('floors')->nullable()->after('status');
            $table->decimal('land_katha', 6, 2)->nullable()->after('floors');
            $table->unsignedInteger('floor_area_sqft')->nullable()->after('land_katha');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropColumn(['status', 'floors', 'land_katha', 'floor_area_sqft']);
        });
    }
};
