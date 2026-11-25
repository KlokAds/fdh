<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hidden support accounts stay out of the client's user list, and two-factor
     * authentication becomes a per-role choice instead of being forced on everyone.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_hidden')->default(false)->after('is_active');
        });

        Schema::table('roles', function (Blueprint $table): void {
            $table->boolean('requires_two_factor')->default(false)->after('guard_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('is_hidden');
        });

        Schema::table('roles', function (Blueprint $table): void {
            $table->dropColumn('requires_two_factor');
        });
    }
};
