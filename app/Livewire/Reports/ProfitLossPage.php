<?php

declare(strict_types=1);

namespace App\Livewire\Reports;

use App\Actions\Report\ComputeProfitLossReportAction;
use Illuminate\Contracts\View\View;

final class ProfitLossPage extends ReportPage
{
    public function render(): View
    {
        $filters = $this->validatedDateRange();

        return $this->renderReport(
            'profit_loss',
            'Profit & Loss Report',
            app(ComputeProfitLossReportAction::class)->handle(
                $filters['start_date'],
                $filters['end_date'],
            ),
        );
    }
}
