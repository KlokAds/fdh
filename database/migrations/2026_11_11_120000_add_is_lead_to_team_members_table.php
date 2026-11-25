<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The head of the team (the managing director) is shown first and in a larger card.
     */
    public function up(): void
    {
        Schema::table('team_members', function (Blueprint $table): void {
            $table->boolean('is_lead')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $table): void {
            $table->dropColumn('is_lead');
        });
    }
};
