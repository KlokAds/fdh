<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

#[Signature('fdh:create-admin {email : Admin email address} {--name=Admin : Display name}')]
#[Description('Create a Super Admin account, or reset an existing account\'s password. The password is asked interactively and never stored in shell history.')]
class CreateAdmin extends Command
{
    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));
        $password = (string) $this->secret('Password (min 12 chars, upper & lower case, number, symbol)');
        $confirm = (string) $this->secret('Confirm password');

        $validator = Validator::make(
            ['email' => $email, 'password' => $password, 'password_confirmation' => $confirm],
            [
                'email' => ['required', 'email'],
                'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::query()->firstOrNew(['email' => $email]);
        $user->name = $user->exists ? $user->name : (string) $this->option('name');
        $user->password = $password;
        $user->is_active = true;
        $user->must_change_password = false;
        $user->save();
        $user->assignRole(Role::findOrCreate(config('fdh.super_role'), 'web'));

        $this->info(($user->wasRecentlyCreated ? 'Admin created' : 'Admin password updated').": {$email}");
        $this->line('Sign in at /'.config('fdh.admin_path').'/login — you will be asked to set up two-factor authentication.');

        return self::SUCCESS;
    }
}
