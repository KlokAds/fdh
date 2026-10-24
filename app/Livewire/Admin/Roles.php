<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

#[Title('Roles & permissions')]
class Roles extends AdminComponent
{
    protected ?string $permission = 'users.manage';

    #[Locked]
    public ?int $editingId = null;

    public bool $showForm = false;

    public string $name = '';

    /** @var list<string> */
    public array $selected = [];

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $role = Role::query()->findOrFail($id);
        abort_if($role->name === config('fdh.super_role'), 403, 'The Super Admin role always has every permission.');

        $this->resetForm();
        $this->editingId = $role->id;
        $this->name = $role->name;
        $this->selected = $role->permissions->pluck('name')->all();
        $this->showForm = true;
    }

    public function save(): void
    {
        $allPermissions = collect(config('fdh.permissions'))->flatMap(fn (array $group): array => array_keys($group))->all();

        $this->validate([
            'name' => ['required', 'string', 'max:50', 'regex:/^[\pL\pN ._-]+$/u', Rule::notIn([config('fdh.super_role')]), Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($this->editingId)],
            'selected' => 'array',
            'selected.*' => ['string', Rule::in($allPermissions)],
        ]);

        // Granting user management lets a role create other admins, so only a Super Admin may grant it.
        if (in_array('users.manage', $this->selected, true)) {
            abort_unless(auth()->user()->isSuperAdmin(), 403, 'Only a Super Admin can grant user management.');
        }

        $role = $this->editingId ? Role::query()->findOrFail($this->editingId) : new Role(['guard_name' => 'web']);
        abort_if($role->name === config('fdh.super_role'), 403);

        $role->name = $this->name;
        $role->save();
        $role->syncPermissions($this->selected);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        ActivityLog::record('role.saved', "Saved role {$role->name}: ".implode(', ', $this->selected));
        $this->resetForm();
        session()->flash('status', 'Role saved.');
    }

    public function delete(int $id): void
    {
        $role = Role::query()->withCount('users')->findOrFail($id);
        abort_if($role->name === config('fdh.super_role'), 403);

        if ($role->users_count > 0) {
            $this->addError('list', "“{$role->name}” is assigned to {$role->users_count} user(s). Move them to another role first.");

            return;
        }

        ActivityLog::record('role.deleted', "Deleted role {$role->name}");
        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->resetForm();
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset('editingId', 'showForm', 'name', 'selected');
        $this->resetErrorBag();
    }

    public function render(): View
    {
        return view('livewire.admin.roles', [
            'roles' => Role::query()->where('guard_name', 'web')->with('permissions')->withCount('users')->orderBy('id')->get(),
            'groups' => config('fdh.permissions'),
            'superRole' => config('fdh.super_role'),
        ]);
    }
}
