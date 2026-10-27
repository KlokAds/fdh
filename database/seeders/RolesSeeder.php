<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesSeeder extends Seeder
{
    /**
     * Create every permission and the default roles. Existing role permissions are left untouched
     * so changes made in the admin panel survive re-seeding.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (config('fdh.permissions') as $permissions) {
            foreach (array_keys($permissions) as $name) {
                Permission::findOrCreate($name, 'web');
            }
        }

        Role::findOrCreate(config('fdh.super_role'), 'web');

        foreach (config('fdh.default_roles') as $roleName => $permissions) {
            $role = Role::query()->where('name', $roleName)->where('guard_name', 'web')->first();

            if (! $role) {
                Role::create(['name' => $roleName, 'guard_name' => 'web'])->syncPermissions($permissions);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
