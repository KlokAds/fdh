<?php

namespace App\Support\Install;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

/**
 * Creates the first Super Admin from the web installer. Two-factor sign-in is set up at the first login.
 */
class FirstAdmin
{
    /**
     * @throws ValidationException
     */
    public function create(string $name, string $email, string $password): User
    {
        Validator::make(['name' => $name, 'email' => $email, 'password' => $password], [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', Password::min(12)->mixedCase()->numbers()->symbols()],
        ])->validate();

        $user = new User(['name' => strip_tags($name), 'email' => strtolower(trim($email)), 'password' => $password]);
        $user->is_active = true;
        $user->must_change_password = false;
        $user->save();
        $user->assignRole(Role::findOrCreate(config('fdh.super_role'), 'web'));

        return $user;
    }
}
