<?php

declare(strict_types=1);

namespace App\Livewire\Reports;

use App\Actions\Report\ComputeCustomerStatementAction;
use Illuminate\Contracts\View\View;

final class CustomerStatementPage extends ReportPage
{
    public function render(): View
    {
        $filters = $this->validatedCustomerStatementFilters();

        $this->syncInvoiceStatuses();

        return $this->renderReport(
            'customer_statement',
            'Customer Statement',
            app(ComputeCustomerStatementAction::class)->handle(
                $filters['customer_id'],
                $filters['start_date'],
                $filters['end_date'],
            ),
        );
    }
}
