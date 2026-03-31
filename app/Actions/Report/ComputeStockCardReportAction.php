<?php

declare(strict_types=1);

namespace App\Actions\Report;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\StockLocation;
use Carbon\Carbon;

final readonly class ComputeStockCardReportAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(?int $productId, ?int $locationId, ?string $startDate, ?string $endDate): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfMonth();

        $products = Product::query()
            ->stockTracked()
            ->active()
            ->orderBy('name')
            ->get();

        $locations = StockLocation::query()
            ->active()
            ->ordered()
            ->get();

        $selectedProduct = $productId === null ? null : Product::query()
            ->with(['baseUnit', 'inventoryStocks', 'defaultPrice'])
            ->find($productId);

        $movements = collect();
        $stockRows = collect();
        $summary = [
            'current_quantity' => 0.0,
            'quantity_in' => 0.0,
            'quantity_out' => 0.0,
        ];

        if ($selectedProduct !== null) {
            $stockRows = $selectedProduct->inventoryStocks()
                ->with('location')
                ->when($locationId !== null, fn ($query) => $query->where('location_id', $locationId))
                ->orderByRaw('case when expiry_date is null then 1 else 0 end')
                ->orderBy('expiry_date')
                ->orderBy('batch_number')
                ->get();

            $movements = InventoryMovement::query()
                ->with(['inventoryStock', 'location', 'product'])
                ->where('product_id', $selectedProduct->id)
                ->when($locationId !== null, fn ($query) => $query->where('location_id', $locationId))
                ->whereBetween('movement_date', [$start, $end])
                ->orderBy('movement_date')
                ->orderBy('id')
                ->get();

            $summary = [
                'current_quantity' => (float) $stockRows->sum('quantity_on_hand'),
                'quantity_in' => (float) $movements->where('direction', \App\Enums\InventoryDirection::In)->sum('quantity'),
                'quantity_out' => (float) $movements->where('direction', \App\Enums\InventoryDirection::Out)->sum('quantity'),
            ];
        }

        return [
            'products' => $products,
            'locations' => $locations,
            'selected_product' => $selectedProduct,
            'selected_location_id' => $locationId,
            'stock_rows' => $stockRows,
            'movements' => $movements,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'summary' => $summary,
        ];
    }
}
