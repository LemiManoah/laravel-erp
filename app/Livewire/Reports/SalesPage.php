<?php

declare(strict_types=1);

namespace App\Livewire\Reports;

use App\Actions\Report\ComputeSalesReportAction;
use Illuminate\Contracts\View\View;

final class SalesPage extends ReportPage
{
    public function render(): View
    {
        $filters = $this->validatedDateRange();

        $this->syncInvoiceStatuses();

        return $this->renderReport(
            'sales',
            'Sales Report',
            app(ComputeSalesReportAction::class)->handle(
                $filters['start_date'],
                $filters['end_date'],
            ),
        );
    }
}
