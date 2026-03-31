<?php

declare(strict_types=1);

namespace App\Livewire\Inventory\Monitoring;

use App\Models\InventoryStock;
use App\Models\Product;
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
        $products = Product::query()
            ->with(['baseUnit', 'inventoryStocks', 'defaultPrice'])
            ->stockTracked()
            ->active()
            ->orderBy('name')
            ->get();

        $lowStockProducts = $products
            ->filter(fn (Product $product): bool => $product->reorder_level !== null && (float) $product->quantity_on_hand <= (float) $product->reorder_level)
            ->values();

        $nearExpiryStocks = InventoryStock::query()
            ->with(['location', 'product'])
            ->available()
            ->nearExpiry()
            ->orderBy('expiry_date')
            ->orderBy('batch_number')
            ->get();

        $expiredStocks = InventoryStock::query()
            ->with(['location', 'product'])
            ->available()
            ->expired()
            ->orderBy('expiry_date')
            ->orderBy('batch_number')
            ->get();

        return view('livewire.inventory.monitoring.index-page', [
            'lowStockProducts' => $lowStockProducts,
            'nearExpiryStocks' => $nearExpiryStocks,
            'expiredStocks' => $expiredStocks,
            'summary' => [
                'tracked_products' => $products->count(),
                'low_stock_products' => $lowStockProducts->count(),
                'near_expiry_rows' => $nearExpiryStocks->count(),
                'expired_rows' => $expiredStocks->count(),
            ],
        ]);
    }
}
