<?php

declare(strict_types=1);

namespace App\Livewire\Reports;

use App\Actions\Report\ComputeOutstandingBalancesReportAction;
use Illuminate\Contracts\View\View;

final class OutstandingBalancesPage extends ReportPage
{
    public function render(): View
    {
        $filters = $this->validatedDateRange();

        $this->syncInvoiceStatuses();

        return $this->renderReport(
            'outstanding_balances',
            'Outstanding Balances',
            app(ComputeOutstandingBalancesReportAction::class)->handle(
                $filters['start_date'],
                $filters['end_date'],
            ),
        );
    }
}
