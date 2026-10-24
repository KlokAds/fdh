<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * An active admin with the given role who has finished account setup (2FA on, no temporary password).
     */
    protected function admin(string $role = 'Super Admin', bool $secured = true): User
    {
        $this->seed(RolesSeeder::class);

        $user = User::factory()->create();
        $user->is_active = true;

        if ($secured) {
            $user->two_factor_secret = 'JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP';
            $user->two_factor_confirmed_at = now();
        }

        $user->save();
        $user->assignRole($role);

        return $user->refresh();
    }
}
