<?php

declare(strict_types=1);

namespace App\Livewire\ActivityLogs;

use App\Models\Activity;
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

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('activity-logs.view'), 403);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function render(): View
    {
        $search = trim($this->search);

        $activities = Activity::query()
            ->with(['causer', 'subject'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $activityQuery) use ($search): void {
                    $activityQuery->where('description', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('event', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('log_name', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('subject_type', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('subject_id', 'like', sprintf('%%%s%%', $search))
                        ->orWhereHas('causer', function (Builder $causerQuery) use ($search): void {
                            $causerQuery->where('name', 'like', sprintf('%%%s%%', $search))
                                ->orWhere('email', 'like', sprintf('%%%s%%', $search));
                        });
                });
            })
            ->latest()
            ->paginate(15);

        return view('livewire.activity_logs.index-page', [
            'activities' => $activities,
        ]);
    }
}
