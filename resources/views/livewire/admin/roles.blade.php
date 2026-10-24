<div class="stack-lg">
    @if ($showForm)
        <section class="card">
            <div class="card-head">
                <h2>{{ $editingId ? 'Edit role' : 'New role' }}</h2>
                <button type="button" class="close-btn" wire:click="cancel" title="Close" aria-label="Close form"><x-icon name="close" /></button>
            </div>
            <form wire:submit="save" class="stack">
                <label class="narrow-field">Role name *<input wire:model="name" maxlength="50">@error('name') <span class="err">{{ $message }}</span> @enderror</label>
                <div class="perm-grid">
                    @foreach ($groups as $groupName => $permissions)
                        <fieldset class="perm-group" wire:key="pg-{{ $loop->index }}">
                            <legend>{{ $groupName }}</legend>
                            @foreach ($permissions as $permission => $label)
                                <label class="check" wire:key="p-{{ $permission }}">
                                    <input type="checkbox" wire:model="selected" value="{{ $permission }}"
                                        @if ($permission === 'users.manage' && ! auth()->user()->isSuperAdmin()) disabled @endif>
                                    {{ $label }}
                                </label>
                            @endforeach
                        </fieldset>
                    @endforeach
                </div>
                @error('selected.*') <span class="err">{{ $message }}</span> @enderror
                <div class="row-actions"><button type="submit" class="btn btn-primary">Save role</button> <button type="button" class="btn btn-ghost" wire:click="cancel">Cancel</button></div>
            </form>
        </section>
    @endif

    <section class="card">
        <div class="card-head">
            <h2>Roles</h2>
            <div class="row-actions">
                <a href="{{ route('admin.users') }}" wire:navigate class="btn btn-ghost btn-sm">← Users</a>
                <button type="button" class="btn btn-primary btn-sm" wire:click="create">+ New role</button>
            </div>
        </div>
        @error('list') <p class="alert alert-error">{{ $message }}</p> @enderror
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Role</th><th>Permissions</th><th>Two-factor</th><th>Users</th><th class="right">Actions</th></tr></thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr wire:key="r-{{ $role->id }}">
                            <td><b>{{ $role->name }}</b></td>
                            <td class="small">
                                @if ($role->name === $superRole)
                                    <span class="pill pill-closed">Everything</span>
                                @else
                                    @forelse ($role->permissions as $permission)
                                        <span class="pill">{{ $permission->name }}</span>
                                    @empty
                                        <span class="muted">None</span>
                                    @endforelse
                                @endif
                            </td>
                            <td class="nowrap">
                                @if ($role->name !== $superRole || auth()->user()->isSuperAdmin())
                                    <button type="button" @class(['pill', 'pill-closed' => $role->requires_two_factor]) wire:click="toggleTwoFactor({{ $role->id }})" title="Click to change">{{ $role->requires_two_factor ? 'Required' : 'Optional' }}</button>
                                @else
                                    <span @class(['pill', 'pill-closed' => $role->requires_two_factor])>{{ $role->requires_two_factor ? 'Required' : 'Optional' }}</span>
                                @endif
                            </td>
                            <td>{{ $role->users_count }}</td>
                            <td class="right nowrap">
                                @unless ($role->name === $superRole)
                                    <button type="button" class="act-btn" wire:click="edit({{ $role->id }})" title="Edit" aria-label="Edit"><x-icon name="edit" /></button>
                                    <button type="button" class="act-btn act-danger" wire:click="delete({{ $role->id }})" data-confirm="Delete the role {{ $role->name }}?" title="Delete" aria-label="Delete"><x-icon name="trash" /></button>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
