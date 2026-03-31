<?php

declare(strict_types=1);

namespace App\Livewire\Purchasing\Orders;

use App\Models\PurchaseOrder;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class ShowPage extends Component
{
    public PurchaseOrder $order;

    public function mount(PurchaseOrder $order): void
    {
        abort_unless(auth()->user()?->can('purchase-orders.view'), 403);

        $this->order = $order->load(['supplier', 'stockLocation', 'items.product.baseUnit', 'creator']);
    }

    public function render(): View
    {
        return view('livewire.purchasing.orders.show-page');
    }
}
