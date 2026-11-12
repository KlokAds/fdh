<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Adds the media library permission to existing installs and gives it to the
     * default Admin and Editor roles when they exist.
     */
    public function up(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::findOrCreate('media.manage', 'web');

        foreach (['Admin', 'Editor'] as $name) {
            Role::query()->where('name', $name)->where('guard_name', 'web')->first()?->givePermissionTo('media.manage');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::query()->where('name', 'media.manage')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
