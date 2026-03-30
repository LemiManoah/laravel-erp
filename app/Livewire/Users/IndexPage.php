<?php

declare(strict_types=1);

namespace App\Livewire\Users;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class IndexPage extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $role = '';

    #[Url(except: '')]
    public string $status = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('viewAny', User::class), 403);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRole(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->role = '';
        $this->status = '';
        $this->resetPage();
    }

    public function render(): View
    {
        $search = trim($this->search);

        $users = User::query()
            ->with('roles')
            ->when($search !== '', static function (Builder $query) use ($search): void {
                $query->where(function (Builder $userQuery) use ($search): void {
                    $userQuery->where('name', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('email', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('phone', 'like', sprintf('%%%s%%', $search));
                });
            })
            ->when($this->role !== '', static fn (Builder $query) => $query->role($this->role))
            ->when($this->status !== '', static fn (Builder $query) => $query->where('is_active', $this->status === 'active'))
            ->latest()
            ->paginate(12);

        return view('livewire.users.index-page', [
            'users' => $users,
            'roles' => Role::query()->orderBy('name')->pluck('name'),
        ]);
    }
}
