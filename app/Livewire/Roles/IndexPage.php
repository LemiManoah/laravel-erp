<?php

declare(strict_types=1);

namespace App\Livewire\Roles;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

final class IndexPage extends Component
{
    use WithPagination;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('viewAny', User::class), 403);
    }

    public function delete(int $roleId): void
    {
        abort_unless(auth()->user()?->can('delete', User::class), 403);

        $role = Role::query()->findOrFail($roleId);

        if ($role->name === 'Admin') {
            session()->flash('error', 'The Admin role cannot be deleted.');

            return;
        }

        $role->delete();
        session()->flash('success', 'Role deleted successfully.');
    }

    public function render(): View
    {
        return view('livewire.roles.index-page', [
            'roles' => Role::query()
                ->withCount('permissions')
                ->orderBy('name')
                ->paginate(15),
        ]);
    }
}
