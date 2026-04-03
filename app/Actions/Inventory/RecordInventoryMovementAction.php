<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Enums\InventoryDirection;
use App\Enums\InventoryMovementType;
use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class RecordInventoryMovementAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(InventoryItem $product, InventoryMovementType $movementType, float $quantity, array $attributes = []): InventoryMovement
    {
        if (! $product->tracks_inventory) {
            throw ValidationException::withMessages([
                'inventory_item_id' => 'The selected inventory item does not track inventory.',
            ]);
        }

        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Inventory quantity must be greater than zero.',
            ]);
        }

        return DB::transaction(function () use ($product, $movementType, $quantity, $attributes): InventoryMovement {
            $product = InventoryItem::query()->lockForUpdate()->findOrFail($product->id);
            $direction = $movementType->direction();
            $conversionRate = max(0.0001, (float) ($attributes['unit_conversion_rate'] ?? 1));
            $baseQuantity = round($quantity * $conversionRate, 2);
            $delta = $baseQuantity * $direction->multiplier();
            $stock = $this->resolveInventoryStock($product, $movementType, $direction, $attributes);

            $this->validateStockUsage($product, $stock, $movementType, $direction, $baseQuantity, $attributes);

            $stockBalance = round((float) $stock->quantity_on_hand + $delta, 2);
            $stock->forceFill([
                'quantity_on_hand' => $stockBalance,
                'unit_cost' => $attributes['unit_cost'] ?? $stock->unit_cost,
                'received_at' => $attributes['received_at'] ?? $stock->received_at,
                'notes' => $attributes['notes'] ?? $stock->notes,
            ])->save();

            $newBalance = round((float) $product->inventoryStocks()->sum('quantity_on_hand'), 2);

            return InventoryMovement::query()->create([
                'tenant_id' => tenant('id'),
                'inventory_item_id' => $product->id,
                'location_id' => $attributes['location_id'] ?? $stock->location_id,
                'inventory_stock_id' => $stock->id,
                'movement_type' => $movementType,
                'direction' => $direction,
                'quantity' => $quantity,
                'unit_id' => $attributes['unit_id'] ?? $product->base_unit_id,
                'unit_conversion_rate' => $conversionRate,
                'balance_after' => $newBalance,
                'unit_cost' => $attributes['unit_cost'] ?? null,
                'reference_type' => $attributes['reference_type'] ?? null,
                'reference_id' => $attributes['reference_id'] ?? null,
                'movement_date' => $attributes['movement_date'] ?? now(),
                'notes' => $attributes['notes'] ?? null,
                'created_by' => $attributes['created_by'] ?? auth()->id(),
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function resolveInventoryStock(InventoryItem $product, InventoryMovementType $movementType, InventoryDirection $direction, array $attributes): InventoryStock
    {
        $stock = $attributes['inventory_stock'] ?? null;

        if ($stock instanceof InventoryStock) {
            return $stock;
        }

        if (isset($attributes['inventory_stock_id']) && filled($attributes['inventory_stock_id'])) {
            return InventoryStock::query()->lockForUpdate()->findOrFail((int) $attributes['inventory_stock_id']);
        }

        $locationId = isset($attributes['location_id']) && filled($attributes['location_id'])
            ? (int) $attributes['location_id']
            : null;

        if ($direction === InventoryDirection::In) {
            if ($product->has_expiry) {
                $batchNumber = trim((string) ($attributes['batch_number'] ?? ''));
                $expiryDate = $attributes['expiry_date'] ?? null;

                if ($batchNumber === '') {
                    throw ValidationException::withMessages([
                        'batch_number' => 'A batch number is required for this item.',
                    ]);
                }

                if (blank($expiryDate)) {
                    throw ValidationException::withMessages([
                        'expiry_date' => 'An expiry date is required for this item.',
                    ]);
                }

                return InventoryStock::query()->lockForUpdate()->firstOrCreate(
                    [
                        'tenant_id' => tenant('id'),
                        'inventory_item_id' => $product->id,
                        'location_id' => $locationId,
                        'batch_number' => $batchNumber,
                    ],
                    [
                        'expiry_date' => $expiryDate,
                        'received_at' => $attributes['received_at'] ?? ($attributes['movement_date'] ?? now()),
                        'unit_cost' => $attributes['unit_cost'] ?? null,
                        'notes' => $attributes['notes'] ?? null,
                        'quantity_on_hand' => 0,
                    ]
                );
            }

            return InventoryStock::query()->lockForUpdate()->firstOrCreate(
                [
                    'tenant_id' => tenant('id'),
                    'inventory_item_id' => $product->id,
                    'location_id' => $locationId,
                    'batch_number' => null,
                ],
                [
                    'received_at' => $attributes['received_at'] ?? ($attributes['movement_date'] ?? now()),
                    'unit_cost' => $attributes['unit_cost'] ?? null,
                    'notes' => $attributes['notes'] ?? null,
                    'quantity_on_hand' => 0,
                ]
            );
        }

        if ($product->has_expiry) {
            throw ValidationException::withMessages([
                'inventory_stock_id' => 'Choose the stock record to issue from for expiring items.',
            ]);
        }

        $existingStock = InventoryStock::query()->lockForUpdate()
            ->where('inventory_item_id', $product->id)
            ->when($locationId !== null, fn ($query) => $query->where('location_id', $locationId))
            ->whereNull('batch_number')
            ->first();

        if ($existingStock !== null) {
            return $existingStock;
        }

        throw ValidationException::withMessages([
            'quantity' => sprintf('No available stock record exists for %s in the selected location.', $product->name),
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function validateStockUsage(InventoryItem $product, InventoryStock $stock, InventoryMovementType $movementType, InventoryDirection $direction, float $baseQuantity, array $attributes): void
    {
        if ((int) $stock->inventory_item_id !== $product->id) {
            throw ValidationException::withMessages([
                'inventory_stock_id' => 'The selected stock record does not belong to the selected inventory item.',
            ]);
        }

        if (isset($attributes['location_id']) && $attributes['location_id'] !== null && (int) $stock->location_id !== (int) $attributes['location_id']) {
            throw ValidationException::withMessages([
                'location_id' => 'The selected stock record does not belong to the selected stock location.',
            ]);
        }

        if ($product->has_expiry) {
            if (blank($stock->batch_number)) {
                throw ValidationException::withMessages([
                    'inventory_stock_id' => 'Expiring items must use a stock record with batch details.',
                ]);
            }

            if ($stock->expiry_date === null) {
                throw ValidationException::withMessages([
                    'inventory_stock_id' => 'Expiring items must use a stock record with an expiry date.',
                ]);
            }
        }

        if ($direction === InventoryDirection::Out && ((float) $stock->quantity_on_hand - $baseQuantity) < 0) {
            throw ValidationException::withMessages([
                'inventory_stock_id' => sprintf('The selected stock record does not have enough quantity for %s.', $product->name),
            ]);
        }

        if ($movementType === InventoryMovementType::SaleIssue && $stock->isExpired()) {
            throw ValidationException::withMessages([
                'inventory_stock_id' => sprintf('Batch %s is expired and cannot be sold.', $stock->batch_number),
            ]);
        }
    }
}
