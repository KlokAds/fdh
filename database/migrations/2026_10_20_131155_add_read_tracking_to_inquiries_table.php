<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->timestamp('read_at')->nullable()->after('status');
            $table->string('read_via', 20)->nullable()->after('read_at');
            $table->timestamp('emailed_at')->nullable()->after('read_via');
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn(['read_at', 'read_via', 'emailed_at']);
        });
    }
};
