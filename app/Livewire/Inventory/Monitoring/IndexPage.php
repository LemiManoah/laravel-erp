<?php

declare(strict_types=1);

namespace App\Livewire\Inventory\Monitoring;

use App\Models\InventoryStock;
use App\Models\InventoryItem;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class IndexPage extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()?->can('inventory-stocks.view'), 403);
    }

    public function render(): View
    {
        $inventoryItems = InventoryItem::query()
            ->with(['baseUnit', 'inventoryStocks', 'defaultPrice'])
            ->stockTracked()
            ->active()
            ->orderBy('name')
            ->get();

        $lowStockInventoryItems = $inventoryItems
            ->filter(fn (InventoryItem $inventoryItem): bool => $inventoryItem->reorder_level !== null && (float) $inventoryItem->quantity_on_hand <= (float) $inventoryItem->reorder_level)
            ->values();

        $nearExpiryStocks = InventoryStock::query()
            ->with(['location', 'inventoryItem'])
            ->available()
            ->nearExpiry()
            ->orderBy('expiry_date')
            ->orderBy('batch_number')
            ->get();

        $expiredStocks = InventoryStock::query()
            ->with(['location', 'inventoryItem'])
            ->available()
            ->expired()
            ->orderBy('expiry_date')
            ->orderBy('batch_number')
            ->get();

        return view('livewire.inventory.monitoring.index-page', [
            'lowStockInventoryItems' => $lowStockInventoryItems,
            'nearExpiryStocks' => $nearExpiryStocks,
            'expiredStocks' => $expiredStocks,
            'summary' => [
                'tracked_inventory_items' => $inventoryItems->count(),
                'low_stock_inventory_items' => $lowStockInventoryItems->count(),
                'near_expiry_rows' => $nearExpiryStocks->count(),
                'expired_rows' => $expiredStocks->count(),
            ],
        ]);
    }
}
