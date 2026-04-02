<?php

declare(strict_types=1);

namespace App\Livewire\Roles;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class CreatePage extends Component
{
    public string $name = '';
    public array $selectedPermissions = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('create', User::class), 403);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', tenant()->unique('roles', 'name')],
            'selectedPermissions' => ['nullable', 'array'],
        ];
    }

    public function save(): mixed
    {
        abort_unless(auth()->user()?->can('create', User::class), 403);

        $this->validate();

        $role = Role::create(['name' => trim($this->name), 'guard_name' => 'web']);

        if ($this->selectedPermissions !== []) {
            $role->syncPermissions($this->selectedPermissions);
        }

        session()->flash('success', 'Role created successfully.');

        return $this->redirectRoute('roles.index');
    }

    public function render(): View
    {
        $permissions = Permission::all()->groupBy(fn ($p) => explode('.', $p->name)[0]);

        return view('livewire.roles.create-page', compact('permissions'));
    }
}
