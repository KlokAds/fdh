<form wire:submit="verify" class="stack" novalidate>
    <h1 class="auth-title">Two-factor verification</h1>
    @if ($useRecovery)
        <p class="muted">Enter one of your saved recovery codes. Each code works only once.</p>
        <label>Recovery code
            <input type="text" wire:model="code" autocomplete="off" maxlength="20" required autofocus>
        </label>
    @else
        <p class="muted">Open your authenticator app and enter the 6-digit code for FDH.</p>
        <label>Authentication code
            <input type="text" wire:model="code" inputmode="numeric" autocomplete="one-time-code" maxlength="7" pattern="[0-9 ]*" required autofocus class="code-input">
        </label>
    @endif
    @error('code') <p class="alert alert-error" role="alert">{{ $message }}</p> @enderror
    <button type="submit" class="btn btn-primary btn-block">Verify</button>
    <button type="button" class="link-btn" wire:click="toggleRecovery">
        {{ $useRecovery ? 'Use authenticator code instead' : 'Lost your phone? Use a recovery code' }}
    </button>
    <a href="{{ route('admin.login') }}" class="link-btn">← Back to sign in</a>
</form>
