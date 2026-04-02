<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Actions\Dashboard\ComputeDashboardDataAction;
use App\Actions\Invoice\SyncInvoiceStatusesAction;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class Dashboard extends Component
{
    public function mount(SyncInvoiceStatusesAction $syncInvoiceStatuses): void
    {
        abort_unless(auth()->user()?->can('dashboard.view'), 403);

        $syncInvoiceStatuses->handle();
    }

    public function render(ComputeDashboardDataAction $computeDashboardData): View
    {
        return view('livewire.dashboard', $computeDashboardData->handle());
    }
}
