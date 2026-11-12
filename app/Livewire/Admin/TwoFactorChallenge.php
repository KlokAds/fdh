<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\User;
use App\Support\TwoFactor;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('Two-Factor Verification')]
class TwoFactorChallenge extends Component
{
    public string $code = '';

    public bool $useRecovery = false;

    public function mount(): mixed
    {
        if (! $this->pendingUser()) {
            return $this->redirectRoute('admin.login');
        }

        return null;
    }

    public function toggleRecovery(): void
    {
        $this->useRecovery = ! $this->useRecovery;
        $this->reset('code');
        $this->resetErrorBag();
    }

    public function verify(TwoFactor $twoFactor): mixed
    {
        $this->validate(['code' => 'required|string|max:20']);

        $user = $this->pendingUser();
        if (! $user) {
            return $this->redirectRoute('admin.login');
        }

        $key = 'two-factor:'.$user->id.'|'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            ActivityLog::record('2fa.locked', 'Too many 2FA attempts', $user->id);
            $this->addError('code', 'Too many attempts. Try again in '.ceil(RateLimiter::availableIn($key) / 60).' minute(s).');

            return null;
        }

        $valid = $this->useRecovery
            ? $twoFactor->useRecoveryCode($user, $this->code)
            : $twoFactor->verifyForUser($user, $this->code);

        if (! $valid) {
            RateLimiter::hit($key, 900);
            ActivityLog::record('2fa.failed', 'Invalid 2FA code', $user->id);
            $this->reset('code');
            $this->addError('code', 'That code is not valid.');

            return null;
        }

        RateLimiter::clear($key);
        session()->forget(['login.id', 'login.expires']);
        Auth::login($user);
        session()->regenerate();
        session()->put('admin.last_activity', time());
        $user->forceFill(['last_login_at' => now(), 'last_login_ip' => request()->ip()])->save();
        ActivityLog::record('login.success', $this->useRecovery ? 'Signed in with a recovery code' : 'Signed in with 2FA', $user->id);

        return $this->redirectRoute('admin.dashboard');
    }

    private function pendingUser(): ?User
    {
        $id = session('login.id');
        $expires = (int) session('login.expires', 0);

        if (! $id || $expires < now()->getTimestamp()) {
            session()->forget(['login.id', 'login.expires']);

            return null;
        }

        $user = User::query()->find($id);

        return $user && $user->canAccessAdmin() && $user->hasTwoFactorEnabled() ? $user : null;
    }

    public function render(): View
    {
        return view('livewire.admin.two-factor-challenge');
    }
}
