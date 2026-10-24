<form wire:submit="authenticate" class="stack" novalidate>
    <h1 class="auth-title">Sign in</h1>
    <label>Email
        <input type="email" wire:model="email" autocomplete="username" maxlength="150" required autofocus>
    </label>
    <label>Password
        <input type="password" wire:model="password" autocomplete="current-password" maxlength="200" required>
    </label>
    @error('email') <p class="alert alert-error" role="alert">{{ $message }}</p> @enderror
    @error('password') <p class="alert alert-error" role="alert">{{ $message }}</p> @enderror
    <button type="submit" class="btn btn-primary btn-block" wire:loading.attr="disabled">
        <span wire:loading.remove wire:target="authenticate">Sign in</span>
        <span wire:loading wire:target="authenticate">Checking…</span>
    </button>
</form>
