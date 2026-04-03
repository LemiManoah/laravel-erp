<?php

declare(strict_types=1);

namespace App\Actions\Purchasing;

use App\Actions\Inventory\RecordInventoryMovementAction;
use App\Enums\InventoryMovementType;
use App\Models\InventoryStock;
use App\Models\InventoryItem;
use App\Models\PurchaseReturn;
use Illuminate\Support\Facades\DB;

final readonly class CreatePurchaseReturnAction
{
    public function __construct(
        private RecordInventoryMovementAction $recordInventoryMovement,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function handle(array $attributes, array $items): PurchaseReturn
    {
        return DB::transaction(function () use ($attributes, $items): PurchaseReturn {
            $return = PurchaseReturn::query()->create([
                'tenant_id' => tenant('id'),
                'return_number' => $attributes['return_number'],
                'supplier_id' => $attributes['supplier_id'],
                'purchase_receipt_id' => $attributes['purchase_receipt_id'] ?? null,
                'stock_location_id' => $attributes['stock_location_id'],
                'return_date' => $attributes['return_date'],
                'subtotal_amount' => collect($items)->sum(fn (array $item): float => (float) $item['line_total']),
                'notes' => $attributes['notes'] ?? null,
                'created_by' => $attributes['created_by'] ?? auth()->id(),
            ]);

            foreach ($items as $item) {
                $product = InventoryItem::query()->findOrFail((int) $item['inventory_item_id']);
                $stock = InventoryStock::query()->findOrFail((int) $item['inventory_stock_id']);

                $return->items()->create([
                    'tenant_id' => tenant('id'),
                    'inventory_item_id' => $product->id,
                    'inventory_stock_id' => $stock->id,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'line_total' => $item['line_total'],
                    'notes' => $item['notes'] ?: null,
                ]);

                $this->recordInventoryMovement->handle($product, InventoryMovementType::PurchaseReturn, (float) $item['quantity'], [
                    'location_id' => (int) $return->stock_location_id,
                    'inventory_stock_id' => $stock->id,
                    'movement_date' => $return->return_date?->toDateString().' 00:00:00',
                    'unit_cost' => (float) $item['unit_cost'],
                    'notes' => $item['notes'] ?: sprintf('Returned via purchase return %s', $return->return_number),
                    'reference_type' => 'purchase_return',
                    'reference_id' => $return->id,
                ]);
            }

            return $return->load(['supplier', 'purchaseReceipt', 'stockLocation', 'items.product', 'items.inventoryStock']);
        });
    }
}
