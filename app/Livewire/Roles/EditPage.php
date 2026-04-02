<?php

declare(strict_types=1);

namespace App\Livewire\Roles;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class EditPage extends Component
{
    #[Locked]
    public int $roleId;

    public string $name = '';
    public array $selectedPermissions = [];

    public function mount(Role $role): void
    {
        abort_unless(auth()->user()?->can('update', User::class), 403);

        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
    }

    protected function rules(): array
    {
        $role = Role::query()->find($this->roleId);

        return [
            'name' => ['required', 'string', 'max:255', tenant()->unique('roles', 'name')->ignore($role)],
            'selectedPermissions' => ['nullable', 'array'],
        ];
    }

    public function update(): mixed
    {
        abort_unless(auth()->user()?->can('update', User::class), 403);

        $role = Role::query()->findOrFail($this->roleId);

        if ($role->name === 'Admin') {
            session()->flash('error', 'The Admin role cannot be modified.');

            return $this->redirectRoute('roles.index');
        }

        $this->validate();

        $role->update(['name' => trim($this->name)]);
        $role->syncPermissions($this->selectedPermissions);

        session()->flash('success', 'Role updated successfully.');

        return $this->redirectRoute('roles.index');
    }

    public function render(): View
    {
        $permissions = Permission::all()->groupBy(fn ($p) => explode('.', $p->name)[0]);

        return view('livewire.roles.edit-page', compact('permissions'));
    }
}
