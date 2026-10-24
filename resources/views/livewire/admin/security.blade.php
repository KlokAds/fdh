<div class="grid-2">
    <section class="card">
        <h2>Two-factor authentication</h2>
        @if ($enabled)
            <p class="badge badge-ok">✓ Enabled</p>
            <p class="muted">Each sign-in needs your password <b>and</b> a code from your authenticator app. {{ $recoveryLeft }} recovery code(s) left.</p>

            @if ($recoveryCodes)
                <div class="codes-box">
                    <p><b>Save these recovery codes somewhere safe.</b> They are shown only once. Each one can sign you in a single time if you lose your phone.</p>
                    <ul class="codes">
                        @foreach ($recoveryCodes as $recoveryCode)
                            <li wire:key="rc-{{ $loop->index }}"><code>{{ $recoveryCode }}</code></li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn btn-ghost btn-sm" wire:click="hideCodes">I have saved them</button>
                </div>
            @else
                <form wire:submit="regenerateCodes" class="stack inline-form">
                    <label>Confirm your password to get new recovery codes
                        <input type="password" wire:model="confirm_password_for_codes" autocomplete="current-password">
                    </label>
                    @error('confirm_password_for_codes') <span class="err">{{ $message }}</span> @enderror
                    <button type="submit" class="btn btn-ghost btn-sm">Generate new recovery codes</button>
                </form>
            @endif
        @elseif ($pendingSecret)
            <ol class="steps">
                <li>Install <b>Google Authenticator</b> or <b>Microsoft Authenticator</b> on your phone.</li>
                <li>Scan this QR code with the app:</li>
            </ol>
            <div class="qr">{!! $qrSvg !!}</div>
            <p class="muted small">Can’t scan? Enter this key manually: <code class="secret">{{ chunk_split($pendingSecret, 4, ' ') }}</code></p>
            <form wire:submit="confirmSetup" class="stack">
                <label>3. Enter the 6-digit code from the app
                    <input type="text" wire:model="code" inputmode="numeric" autocomplete="one-time-code" maxlength="7" class="code-input">
                </label>
                @error('code') <span class="err">{{ $message }}</span> @enderror
                <button type="submit" class="btn btn-primary">Confirm &amp; enable</button>
            </form>
        @else
            <p class="badge badge-warn">Not enabled</p>
            <p class="muted">{{ auth()->user()->requiresTwoFactor() ? 'Your role requires two-factor authentication.' : 'Two-factor authentication is optional for your role, but recommended.' }} It protects the panel even if your password is stolen.</p>
            <button type="button" class="btn btn-primary" wire:click="startSetup">Set up two-factor authentication</button>
        @endif
    </section>

    <section class="card">
        <h2>Change password</h2>
        <form wire:submit="updatePassword" class="stack">
            <label>Current password
                <input type="password" wire:model="current_password" autocomplete="current-password">
                @error('current_password') <span class="err">{{ $message }}</span> @enderror
            </label>
            <label>New password
                <input type="password" wire:model="password" autocomplete="new-password">
                @error('password') <span class="err">{{ $message }}</span> @enderror
            </label>
            <label>Confirm new password
                <input type="password" wire:model="password_confirmation" autocomplete="new-password">
            </label>
            <p class="muted small">At least 12 characters with upper &amp; lower case letters, a number and a symbol. Changing it signs out all other devices.</p>
            <button type="submit" class="btn btn-primary">Update password</button>
        </form>
    </section>
</div>
