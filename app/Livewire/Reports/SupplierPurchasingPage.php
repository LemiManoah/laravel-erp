<?php

declare(strict_types=1);

namespace App\Livewire\Reports;

use App\Actions\Report\ComputeSupplierPurchasingReportAction;
use Illuminate\Contracts\View\View;

final class SupplierPurchasingPage extends ReportPage
{
    public function render(): View
    {
        $filters = $this->validatedSupplierPurchasingFilters();

        return $this->renderReport(
            'supplier_purchasing',
            'Supplier Purchasing Report',
            app(ComputeSupplierPurchasingReportAction::class)->handle(
                $filters['supplier_id'],
                $filters['start_date'],
                $filters['end_date'],
            ),
        );
    }
}
