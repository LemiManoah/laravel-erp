<?php

declare(strict_types=1);

namespace App\Actions\Report;

use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\StockLocation;

final readonly class ComputeInventoryStatusReportAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(): array
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
            ->with(['product', 'location'])
            ->available()
            ->nearExpiry()
            ->orderBy('expiry_date')
            ->get();

        $expiredStocks = InventoryStock::query()
            ->with(['product', 'location'])
            ->available()
            ->expired()
            ->orderBy('expiry_date')
            ->get();

        $stockByLocation = StockLocation::query()
            ->with(['inventoryStocks.product'])
            ->active()
            ->ordered()
            ->get()
            ->map(function (StockLocation $location): array {
                $rows = $location->inventoryStocks;

                return [
                    'location' => $location,
                    'stock_rows' => $rows->count(),
                    'tracked_products' => $rows->pluck('product_id')->filter()->unique()->count(),
                    'total_quantity' => $rows->sum(fn (InventoryStock $stock): float => (float) $stock->quantity_on_hand),
                ];
            });

        return [
            'products' => $products,
            'low_stock_products' => $lowStockProducts,
            'near_expiry_stocks' => $nearExpiryStocks,
            'expired_stocks' => $expiredStocks,
            'stock_by_location' => $stockByLocation,
            'summary' => [
                'tracked_products' => $products->count(),
                'low_stock_products' => $lowStockProducts->count(),
                'near_expiry_rows' => $nearExpiryStocks->count(),
                'expired_rows' => $expiredStocks->count(),
            ],
        ];
    }
}
