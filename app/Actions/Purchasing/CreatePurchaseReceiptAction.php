<?php

declare(strict_types=1);

namespace App\Actions\Purchasing;

use App\Actions\Inventory\RecordInventoryMovementAction;
use App\Enums\InventoryMovementType;
use App\Enums\PurchaseReceiptStatus;
use App\Models\InventoryItem;
use App\Models\PurchaseReceipt;
use Illuminate\Support\Facades\DB;

final readonly class CreatePurchaseReceiptAction
{
    public function __construct(
        private RecordInventoryMovementAction $recordInventoryMovement,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function handle(array $attributes, array $items): PurchaseReceipt
    {
        return DB::transaction(function () use ($attributes, $items): PurchaseReceipt {
            $receipt = PurchaseReceipt::query()->create([
                'tenant_id' => tenant('id'),
                'receipt_number' => $attributes['receipt_number'],
                'supplier_id' => $attributes['supplier_id'],
                'purchase_order_id' => $attributes['purchase_order_id'] ?? null,
                'stock_location_id' => $attributes['stock_location_id'],
                'receipt_date' => $attributes['receipt_date'],
                'status' => PurchaseReceiptStatus::Posted,
                'subtotal_amount' => collect($items)->sum(fn (array $item): float => (float) $item['line_total']),
                'notes' => $attributes['notes'] ?? null,
                'created_by' => $attributes['created_by'] ?? auth()->id(),
                'posted_at' => now(),
            ]);

            foreach ($items as $item) {
                $product = InventoryItem::query()->findOrFail((int) $item['inventory_item_id']);

                $receiptItem = $receipt->items()->create([
                    'tenant_id' => tenant('id'),
                    'inventory_item_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'line_total' => $item['line_total'],
                    'batch_number' => $item['batch_number'] ?: null,
                    'expiry_date' => $item['expiry_date'] ?: null,
                    'notes' => $item['notes'] ?: null,
                ]);

                $this->recordInventoryMovement->handle($product, InventoryMovementType::PurchaseReceipt, (float) $item['quantity'], [
                    'location_id' => (int) $receipt->stock_location_id,
                    'batch_number' => $item['batch_number'] ?: null,
                    'expiry_date' => $item['expiry_date'] ?: null,
                    'received_at' => $receipt->receipt_date?->toDateString(),
                    'movement_date' => $receipt->receipt_date?->toDateString().' 00:00:00',
                    'unit_cost' => (float) $item['unit_cost'],
                    'notes' => $item['notes'] ?: sprintf('Received via purchase receipt %s', $receipt->receipt_number),
                    'reference_type' => 'purchase_receipt',
                    'reference_id' => $receipt->id,
                ]);
            }

            if (! empty($attributes['purchase_order_id'])) {
                $receipt->purchaseOrder?->update([
                    'status' => \App\Enums\PurchaseOrderStatus::Received,
                ]);
            }

            return $receipt->load(['supplier', 'purchaseOrder', 'stockLocation', 'items.inventoryItem']);
        });
    }
}
