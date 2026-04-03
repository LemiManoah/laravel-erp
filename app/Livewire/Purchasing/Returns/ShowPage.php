<?php

declare(strict_types=1);

namespace App\Livewire\Purchasing\Returns;

use App\Models\PurchaseReturn;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class ShowPage extends Component
{
    public PurchaseReturn $purchaseReturn;

    public function mount(PurchaseReturn $purchaseReturn): void
    {
        abort_unless(auth()->user()?->can('purchase-returns.view'), 403);

        $this->purchaseReturn = $purchaseReturn->load(['supplier', 'purchaseReceipt', 'stockLocation', 'items.inventoryItem.baseUnit', 'creator']);
    }

    public function render(): View
    {
        return view('livewire.purchasing.returns.show-page');
    }
}
