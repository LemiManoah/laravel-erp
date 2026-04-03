<?php

declare(strict_types=1);

namespace App\Livewire\Reports;

use App\Actions\Report\ComputePaymentsReportAction;
use Illuminate\Contracts\View\View;

final class PaymentsPage extends ReportPage
{
    public function render(): View
    {
        $filters = $this->validatedDateRange();

        return $this->renderReport(
            'payments',
            'Payments Report',
            app(ComputePaymentsReportAction::class)->handle(
                $filters['start_date'],
                $filters['end_date'],
            ),
        );
    }
}
