<div class="stack-lg">
    @if ($temporaryPassword)
        <section class="card codes-box">
            <p><b>Temporary password for {{ $temporaryFor }}</b> — copy it now, it will not be shown again. Share it privately (in person or by phone). They must change it and set up two-factor authentication at first sign-in.</p>
            <code class="temp-pass">{{ $temporaryPassword }}</code>
            <div class="row-actions"><button type="button" class="btn btn-ghost btn-sm" wire:click="dismissPassword">Done, hide it</button></div>
        </section>
    @endif

    @if ($showForm)
        <section class="card narrow">
            <div class="card-head">
                <h2>{{ $editingId ? 'Edit user' : 'Invite a team member' }}</h2>
                <button type="button" class="link-btn" wire:click="cancel">Cancel ✕</button>
            </div>
            <form wire:submit="save" class="stack">
                <div class="row">
                    <label>Name *<input wire:model="name" maxlength="100" autocomplete="off">@error('name') <span class="err">{{ $message }}</span> @enderror</label>
                    <label>Email *<input type="email" wire:model="email" maxlength="150" autocomplete="off">@error('email') <span class="err">{{ $message }}</span> @enderror</label>
                </div>
                <label>Role *
                    <select wire:model="role">
                        <option value="">Select a role</option>
                        @foreach ($roles as $roleName)
                            <option value="{{ $roleName }}">{{ $roleName }}</option>
                        @endforeach
                    </select>
                    @error('role') <span class="err">{{ $message }}</span> @enderror
                </label>
                @unless ($editingId)
                    <p class="muted small">A strong temporary password is generated for you to share. No password is ever emailed.</p>
                @endunless
                <div class="row-actions"><button type="submit" class="btn btn-primary">{{ $editingId ? 'Save changes' : 'Create user' }}</button></div>
            </form>
        </section>
    @endif

    <section class="card">
        <div class="card-head">
            <h2>Admin users</h2>
            <div class="row-actions">
                <a href="{{ route('admin.roles') }}" wire:navigate class="btn btn-ghost btn-sm">Roles &amp; permissions</a>
                <button type="button" class="btn btn-primary btn-sm" wire:click="create">+ Add user</button>
            </div>
        </div>
        @error('list') <p class="alert alert-error">{{ $message }}</p> @enderror
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>User</th><th>Role</th><th>Security</th><th>Last sign-in</th><th class="right">Actions</th></tr></thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr wire:key="u-{{ $user->id }}" @class(['is-muted' => ! $user->is_active])>
                            <td><b>{{ $user->name }}</b> @if ($user->id === auth()->id())<span class="pill">You</span>@endif<br><span class="muted small">{{ $user->email }}</span></td>
                            <td>{{ $user->roles->pluck('name')->implode(', ') ?: '—' }}</td>
                            <td>
                                @if (! $user->is_active)
                                    <span class="pill pill-spam">Deactivated</span>
                                @else
                                    <span @class(['pill', 'pill-closed' => $user->hasTwoFactorEnabled(), 'pill-new' => ! $user->hasTwoFactorEnabled()])>{{ $user->hasTwoFactorEnabled() ? '2FA on' : '2FA pending' }}</span>
                                    @if ($user->must_change_password)<span class="pill pill-new">Temp password</span>@endif
                                @endif
                            </td>
                            <td class="small">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}<br><span class="muted">{{ $user->last_login_ip }}</span></td>
                            <td class="right">
                                @if ($user->id !== auth()->id())
                                    <div class="row-actions end">
                                        <button type="button" class="btn btn-ghost btn-sm" wire:click="edit({{ $user->id }})">Edit</button>
                                        <button type="button" class="btn btn-ghost btn-sm" wire:click="resetPassword({{ $user->id }})" wire:confirm="Generate a new temporary password for {{ $user->email }}? They will be signed out.">Reset password</button>
                                        @if ($user->hasTwoFactorEnabled())
                                            <button type="button" class="btn btn-ghost btn-sm" wire:click="resetTwoFactor({{ $user->id }})" wire:confirm="Reset two-factor for {{ $user->email }}? Only do this if they lost their phone.">Reset 2FA</button>
                                        @endif
                                        <button type="button" class="btn btn-ghost btn-sm" wire:click="toggleActive({{ $user->id }})">{{ $user->is_active ? 'Deactivate' : 'Activate' }}</button>
                                        <button type="button" class="btn btn-danger btn-sm" wire:click="delete({{ $user->id }})" wire:confirm="Delete {{ $user->email }} permanently?">Delete</button>
                                    </div>
                                @else
                                    <a href="{{ route('admin.security') }}" wire:navigate class="btn btn-ghost btn-sm">My security</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
