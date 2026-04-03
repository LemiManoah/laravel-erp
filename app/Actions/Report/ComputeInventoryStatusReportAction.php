<?php

declare(strict_types=1);

namespace App\Actions\Report;

use App\Models\InventoryStock;
use App\Models\InventoryItem;
use App\Models\StockLocation;

final readonly class ComputeInventoryStatusReportAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(): array
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
            ->with(['inventoryItem', 'location'])
            ->available()
            ->nearExpiry()
            ->orderBy('expiry_date')
            ->get();

        $expiredStocks = InventoryStock::query()
            ->with(['inventoryItem', 'location'])
            ->available()
            ->expired()
            ->orderBy('expiry_date')
            ->get();

        $stockByLocation = StockLocation::query()
            ->with(['inventoryStocks.inventoryItem'])
            ->active()
            ->ordered()
            ->get()
            ->map(function (StockLocation $location): array {
                $rows = $location->inventoryStocks;

                return [
                    'location' => $location,
                    'stock_rows' => $rows->count(),
                    'tracked_inventory_items' => $rows->pluck('inventory_item_id')->filter()->unique()->count(),
                    'total_quantity' => $rows->sum(fn (InventoryStock $stock): float => (float) $stock->quantity_on_hand),
                ];
            });

        return [
            'inventory_items' => $inventoryItems,
            'low_stock_inventory_items' => $lowStockInventoryItems,
            'near_expiry_stocks' => $nearExpiryStocks,
            'expired_stocks' => $expiredStocks,
            'stock_by_location' => $stockByLocation,
            'summary' => [
                'tracked_inventory_items' => $inventoryItems->count(),
                'low_stock_inventory_items' => $lowStockInventoryItems->count(),
                'near_expiry_rows' => $nearExpiryStocks->count(),
                'expired_rows' => $expiredStocks->count(),
            ],
        ];
    }
}
