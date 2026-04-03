<?php

declare(strict_types=1);

namespace App\Livewire\Reports;

use Illuminate\Contracts\View\View;

final class IndexPage extends ReportPage
{
    public function render(): View
    {
        return $this->renderReport('index', 'Reports');
    }
}
