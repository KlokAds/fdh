<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('Admin Sign In')]
class Login extends Component
{
    #[Validate('required|string|email|max:150')]
    public string $email = '';

    #[Validate('required|string|max:200')]
    public string $password = '';

    public function authenticate(): mixed
    {
        $this->validate();

        $email = Str::lower(trim($this->email));
        $accountKey = 'login:'.sha1($email.'|'.request()->ip());
        $ipKey = 'login-ip:'.request()->ip();
        $decay = config('fdh.login_decay_seconds');

        if (RateLimiter::tooManyAttempts($accountKey, config('fdh.login_max_attempts'))
            || RateLimiter::tooManyAttempts($ipKey, config('fdh.login_ip_max_attempts'))) {
            $seconds = max(RateLimiter::availableIn($accountKey), RateLimiter::availableIn($ipKey));
            ActivityLog::record('login.locked', "Locked out: {$email}");
            $this->reset('password');
            $this->addError('email', 'Too many sign-in attempts. Please try again in '.ceil($seconds / 60).' minute(s).');

            return null;
        }

        $user = User::query()->where('email', $email)->first();

        // Always run a hash check so response time does not reveal whether the email exists.
        $passwordMatches = Hash::check($this->password, $user?->password ?? '$2y$12$'.str_repeat('a', 53));

        if (! $user || ! $passwordMatches || ! $user->canAccessAdmin()) {
            RateLimiter::hit($accountKey, $decay);
            RateLimiter::hit($ipKey, $decay);
            ActivityLog::record('login.failed', "Failed sign-in: {$email}", $user?->id);
            $this->reset('password');
            $this->addError('email', 'These credentials do not match our records.');

            return null;
        }

        RateLimiter::clear($accountKey);
        session()->regenerate();

        if ($user->hasTwoFactorEnabled()) {
            session()->put('login.id', $user->id);
            session()->put('login.expires', now()->addMinutes(5)->getTimestamp());
            ActivityLog::record('login.password_ok', 'Password accepted, awaiting 2FA', $user->id);

            return $this->redirectRoute('admin.two-factor');
        }

        Auth::login($user);
        session()->regenerate();
        session()->put('admin.last_activity', time());
        $user->forceFill(['last_login_at' => now(), 'last_login_ip' => request()->ip()])->save();
        ActivityLog::record('login.success', 'Signed in without 2FA', $user->id);

        return $user->needsSecuritySetup() ? $this->redirectRoute('admin.security') : $this->redirectRoute('admin.dashboard');
    }

    public function render(): View
    {
        return view('livewire.admin.login');
    }
}
