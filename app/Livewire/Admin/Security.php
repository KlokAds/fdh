<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Support\TwoFactor;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;

#[Title('Account Security')]
class Security extends AdminComponent
{
    public string $code = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $confirm_password_for_codes = '';

    /** @var list<string> */
    public array $recoveryCodes = [];

    protected function requiresSecureAccount(): bool
    {
        return false;
    }

    public function startSetup(): void
    {
        if (auth()->user()->hasTwoFactorEnabled()) {
            return;
        }

        session()->put('two_factor.pending_secret', app(TwoFactor::class)->generateSecret());
    }

    public function confirmSetup(TwoFactor $twoFactor): void
    {
        $this->validate(['code' => 'required|string|max:10']);

        $user = auth()->user();
        $secret = session('two_factor.pending_secret');
        $key = 'two-factor-setup:'.$user->id;

        if (! $secret || $user->hasTwoFactorEnabled()) {
            return;
        }

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('code', 'Too many attempts. Please wait a few minutes.');

            return;
        }

        if (! $twoFactor->verifySecret($secret, $this->code)) {
            RateLimiter::hit($key, 600);
            $this->addError('code', 'That code is not valid. Check the time on your phone and try again.');

            return;
        }

        RateLimiter::clear($key);
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_last_timestamp' => null,
        ])->save();
        session()->forget('two_factor.pending_secret');

        $this->recoveryCodes = $twoFactor->regenerateRecoveryCodes($user);
        $this->reset('code');
        ActivityLog::record('2fa.enabled', 'Two-factor authentication enabled');
        session()->flash('status', 'Two-factor authentication is on. Save your recovery codes now.');
    }

    public function regenerateCodes(TwoFactor $twoFactor): void
    {
        $this->validate(['confirm_password_for_codes' => 'required|current_password']);

        $this->recoveryCodes = $twoFactor->regenerateRecoveryCodes(auth()->user());
        $this->reset('confirm_password_for_codes');
        ActivityLog::record('2fa.recovery_regenerated', 'Recovery codes regenerated');
    }

    public function hideCodes(): void
    {
        $this->recoveryCodes = [];
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', 'different:current_password', Password::defaults()],
        ]);

        $user = auth()->user();
        $user->forceFill(['password' => $this->password, 'must_change_password' => false])->save();

        // Sign out every other session for this account.
        DB::table('sessions')->where('user_id', $user->id)->where('id', '!=', session()->getId())->delete();
        session()->regenerate();

        $this->reset('current_password', 'password', 'password_confirmation');
        ActivityLog::record('password.changed', 'Password changed; other sessions signed out');
        session()->flash('status', 'Password updated. All other devices have been signed out.');
    }

    public function render(TwoFactor $twoFactor): View
    {
        $user = auth()->user();
        $pending = session('two_factor.pending_secret');

        return view('livewire.admin.security', [
            'enabled' => $user->hasTwoFactorEnabled(),
            'pendingSecret' => $pending,
            'qrSvg' => $pending && ! $user->hasTwoFactorEnabled() ? $twoFactor->qrCodeSvg($user, $pending) : null,
            'recoveryLeft' => count($user->two_factor_recovery_codes ?? []),
        ]);
    }
}
