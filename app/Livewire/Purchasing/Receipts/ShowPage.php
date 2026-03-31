<?php

declare(strict_types=1);

namespace App\Livewire\Purchasing\Receipts;

use App\Models\PurchaseReceipt;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class ShowPage extends Component
{
    public PurchaseReceipt $receipt;

    public function mount(PurchaseReceipt $receipt): void
    {
        abort_unless(auth()->user()?->can('purchase-receipts.view'), 403);

        $this->receipt = $receipt->load(['supplier', 'stockLocation', 'items.product.baseUnit', 'creator']);
    }

    public function render(): View
    {
        return view('livewire.purchasing.receipts.show-page');
    }
}
