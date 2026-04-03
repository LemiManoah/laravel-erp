<?php

declare(strict_types=1);

namespace App\Livewire\Reports;

use App\Actions\Report\ComputeExpensesReportAction;
use Illuminate\Contracts\View\View;

final class ExpensesPage extends ReportPage
{
    public function render(): View
    {
        $filters = $this->validatedDateRange();

        return $this->renderReport(
            'expenses',
            'Expense Report',
            app(ComputeExpensesReportAction::class)->handle(
                $filters['start_date'],
                $filters['end_date'],
            ),
        );
    }
}
