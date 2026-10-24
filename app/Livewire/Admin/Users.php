<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Spatie\Permission\Models\Role;

#[Title('Admin users')]
class Users extends AdminComponent
{
    protected ?string $permission = 'users.manage';

    #[Locked]
    public ?int $editingId = null;

    public bool $showForm = false;

    public string $name = '';

    public string $email = '';

    public string $role = '';

    /** Shown once after creating a user or resetting a password. */
    public ?string $temporaryPassword = null;

    public ?string $temporaryFor = null;

    public function create(): void
    {
        $this->resetForm();
        $this->role = 'Editor';
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $user = User::query()->findOrFail($id);
        $this->guardTarget($user);
        $this->resetForm();
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = (string) $user->roles->first()?->name;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->email = Str::lower(trim($this->email));

        $this->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($this->editingId)],
            'role' => ['required', Rule::exists('roles', 'name')->where('guard_name', 'web')],
        ]);

        $this->guardRoleChange();

        if ($this->editingId) {
            $user = User::query()->findOrFail($this->editingId);
            $user->fill(['name' => strip_tags($this->name), 'email' => $this->email])->save();
            $user->syncRoles([$this->role]);
            ActivityLog::record('user.updated', "Updated {$user->email} (role: {$this->role})");
            session()->flash('status', 'User updated.');
        } else {
            $password = $this->generatePassword();
            $user = new User(['name' => strip_tags($this->name), 'email' => $this->email, 'password' => $password]);
            $user->is_active = true;
            $user->must_change_password = true;
            $user->save();
            $user->assignRole($this->role);

            $this->temporaryPassword = $password;
            $this->temporaryFor = $user->email;
            ActivityLog::record('user.created', "Created {$user->email} (role: {$this->role})");
        }

        $this->resetForm(keepTemporary: true);
    }

    public function toggleActive(int $id): void
    {
        $user = User::query()->findOrFail($id);
        $this->guardTarget($user);
        abort_if($user->id === auth()->id(), 403, 'You cannot deactivate your own account.');

        if ($user->is_active && $user->isSuperAdmin() && $this->activeSuperAdmins() <= 1) {
            $this->addError('list', 'At least one active Super Admin is required.');

            return;
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        if (! $user->is_active) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        ActivityLog::record($user->is_active ? 'user.activated' : 'user.deactivated', ($user->is_active ? 'Activated ' : 'Deactivated ').$user->email);
    }

    public function resetPassword(int $id): void
    {
        $user = User::query()->findOrFail($id);
        $this->guardTarget($user);
        abort_if($user->id === auth()->id(), 403, 'Use Account security to change your own password.');

        $password = $this->generatePassword();
        $user->password = $password;
        $user->must_change_password = true;
        $user->save();
        DB::table('sessions')->where('user_id', $user->id)->delete();

        $this->temporaryPassword = $password;
        $this->temporaryFor = $user->email;
        ActivityLog::record('user.password_reset', "Reset password for {$user->email}");
    }

    public function resetTwoFactor(int $id): void
    {
        $user = User::query()->findOrFail($id);
        $this->guardTarget($user);
        abort_if($user->id === auth()->id(), 403);

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_last_timestamp' => null,
        ])->save();
        DB::table('sessions')->where('user_id', $user->id)->delete();

        ActivityLog::record('user.2fa_reset', "Reset two-factor for {$user->email}");
        session()->flash('status', "Two-factor reset for {$user->email}. They will set it up again at next sign-in.");
    }

    public function delete(int $id): void
    {
        $user = User::query()->findOrFail($id);
        $this->guardTarget($user);
        abort_if($user->id === auth()->id(), 403, 'You cannot delete your own account.');

        if ($user->isSuperAdmin() && $this->activeSuperAdmins() <= 1) {
            $this->addError('list', 'At least one active Super Admin is required.');

            return;
        }

        DB::table('sessions')->where('user_id', $user->id)->delete();
        ActivityLog::record('user.deleted', "Deleted {$user->email}");
        $user->delete();
        $this->resetForm();
    }

    public function dismissPassword(): void
    {
        $this->temporaryPassword = null;
        $this->temporaryFor = null;
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    /**
     * Only Super Admins may grant or remove the Super Admin role, nobody may change their own role,
     * and the last active Super Admin cannot be demoted.
     */
    private function guardRoleChange(): void
    {
        $actor = auth()->user();
        $superRole = config('fdh.super_role');
        $target = $this->editingId ? User::query()->findOrFail($this->editingId) : null;

        if ($target && $target->id === $actor->id && ! $target->hasRole($this->role)) {
            abort(403, 'You cannot change your own role.');
        }

        $touchesSuper = $this->role === $superRole || ($target && $target->isSuperAdmin());
        abort_if($touchesSuper && ! $actor->isSuperAdmin(), 403, 'Only a Super Admin can grant or remove the Super Admin role.');

        if ($target && $target->isSuperAdmin() && $this->role !== $superRole && $this->activeSuperAdmins() <= 1) {
            abort(422, 'At least one active Super Admin is required.');
        }
    }

    /**
     * Only a Super Admin may manage another Super Admin's account.
     */
    private function guardTarget(User $user): void
    {
        abort_if($user->isSuperAdmin() && ! auth()->user()->isSuperAdmin(), 403, 'Only a Super Admin can manage a Super Admin account.');
    }

    private function activeSuperAdmins(): int
    {
        return User::role(config('fdh.super_role'))->where('is_active', true)->count();
    }

    private function generatePassword(): string
    {
        // 16 chars guaranteed to satisfy the password policy.
        return Str::password(13, symbols: false).'a#7';
    }

    private function resetForm(bool $keepTemporary = false): void
    {
        $this->reset('editingId', 'showForm', 'name', 'email', 'role');
        if (! $keepTemporary) {
            $this->reset('temporaryPassword', 'temporaryFor');
        }
        $this->resetErrorBag();
    }

    public function render(): View
    {
        $roles = Role::query()->where('guard_name', 'web')->orderBy('name')->pluck('name');

        if (! auth()->user()->isSuperAdmin()) {
            $roles = $roles->reject(fn (string $name): bool => $name === config('fdh.super_role'));
        }

        return view('livewire.admin.users', [
            'users' => User::query()->with('roles')->orderByDesc('is_active')->orderBy('name')->get(),
            'roles' => $roles->values(),
        ]);
    }
}
