<?php

declare(strict_types=1);

namespace App\Actions\Purchasing;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;

final readonly class CreatePurchaseOrderAction
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function handle(array $attributes, array $items): PurchaseOrder
    {
        return DB::transaction(function () use ($attributes, $items): PurchaseOrder {
            $order = PurchaseOrder::query()->create([
                'tenant_id' => tenant('id'),
                'order_number' => $attributes['order_number'],
                'supplier_id' => $attributes['supplier_id'],
                'stock_location_id' => $attributes['stock_location_id'],
                'order_date' => $attributes['order_date'],
                'expected_date' => $attributes['expected_date'] ?? null,
                'status' => $attributes['status'] ?? PurchaseOrderStatus::Ordered,
                'subtotal_amount' => collect($items)->sum(fn (array $item): float => (float) $item['line_total']),
                'notes' => $attributes['notes'] ?? null,
                'created_by' => $attributes['created_by'] ?? auth()->id(),
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'tenant_id' => tenant('id'),
                    'inventory_item_id' => $item['inventory_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'line_total' => $item['line_total'],
                    'notes' => $item['notes'] ?: null,
                ]);
            }

            return $order->load(['supplier', 'stockLocation', 'items.product']);
        });
    }
}
