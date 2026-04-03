<?php

declare(strict_types=1);

namespace App\Livewire\Reports;

use App\Actions\Report\ComputeInventoryStatusReportAction;
use Illuminate\Contracts\View\View;

final class InventoryStatusPage extends ReportPage
{
    public function render(): View
    {
        return $this->renderReport(
            'inventory_status',
            'Inventory Status Report',
            app(ComputeInventoryStatusReportAction::class)->handle(),
        );
    }
}
