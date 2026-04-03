<?php

declare(strict_types=1);

namespace App\Livewire\Reports;

use App\Actions\Report\ComputeStockCardReportAction;
use Illuminate\Contracts\View\View;

final class StockCardPage extends ReportPage
{
    public function render(): View
    {
        $filters = $this->validatedStockCardFilters();

        return $this->renderReport(
            'stock_card',
            'Stock Card Report',
            app(ComputeStockCardReportAction::class)->handle(
                $filters['product_id'],
                $filters['location_id'],
                $filters['start_date'],
                $filters['end_date'],
            ),
        );
    }
}
