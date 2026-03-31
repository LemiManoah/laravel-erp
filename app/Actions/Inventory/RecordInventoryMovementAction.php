<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Enums\InventoryBatchStatus;
use App\Enums\InventoryDirection;
use App\Enums\InventoryMovementType;
use App\Models\InventoryBatch;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class RecordInventoryMovementAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(Product $product, InventoryMovementType $movementType, float $quantity, array $attributes = []): InventoryMovement
    {
        if (! $product->tracks_inventory) {
            throw ValidationException::withMessages([
                'product_id' => 'The selected product does not track inventory.',
            ]);
        }

        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Inventory quantity must be greater than zero.',
            ]);
        }

        return DB::transaction(function () use ($product, $movementType, $quantity, $attributes): InventoryMovement {
            $product = Product::query()->lockForUpdate()->findOrFail($product->id);
            $direction = $movementType->direction();
            $conversionRate = max(0.0001, (float) ($attributes['unit_conversion_rate'] ?? 1));
            $baseQuantity = round($quantity * $conversionRate, 2);
            $delta = $baseQuantity * $direction->multiplier();
            $batch = $this->resolveBatch($product, $movementType, $direction, $attributes);

            if ($direction === InventoryDirection::Out && ! $product->allow_negative_stock && ((float) $product->quantity_on_hand + $delta) < 0) {
                throw ValidationException::withMessages([
                    'quantity' => sprintf('Insufficient stock for %s. Available quantity is %s.', $product->name, number_format((float) $product->quantity_on_hand, 2)),
                ]);
            }

            if ($batch !== null) {
                $this->validateBatchUsage($product, $batch, $movementType, $direction, $baseQuantity, $attributes);
            }

            $newBalance = round((float) $product->quantity_on_hand + $delta, 2);
            $product->forceFill(['quantity_on_hand' => $newBalance])->save();

            if ($batch !== null) {
                $batchBalance = round((float) $batch->quantity_on_hand + $delta, 2);

                if ($direction === InventoryDirection::Out && ! $product->allow_negative_stock && $batchBalance < 0) {
                    throw ValidationException::withMessages([
                        'batch_id' => sprintf('Batch %s does not have enough stock.', $batch->batch_number),
                    ]);
                }

                $batch->forceFill([
                    'quantity_on_hand' => $batchBalance,
                    'status' => $this->resolveBatchStatus($batch, $batchBalance),
                ])->save();
            }

            return InventoryMovement::query()->create([
                'tenant_id' => tenant('id'),
                'product_id' => $product->id,
                'location_id' => $attributes['location_id'] ?? $batch?->location_id,
                'batch_id' => $batch?->id,
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
    private function resolveBatch(Product $product, InventoryMovementType $movementType, InventoryDirection $direction, array $attributes): ?InventoryBatch
    {
        $batch = $attributes['batch'] ?? null;

        if ($batch instanceof InventoryBatch) {
            return $batch;
        }

        if (isset($attributes['batch_id']) && $attributes['batch_id'] !== null && $attributes['batch_id'] !== '') {
            return InventoryBatch::query()->findOrFail((int) $attributes['batch_id']);
        }

        if ($direction === InventoryDirection::In && ($product->requires_batch_tracking || $product->has_expiry)) {
            $batchNumber = trim((string) ($attributes['batch_number'] ?? ''));
            $expiryDate = $attributes['expiry_date'] ?? null;

            if ($batchNumber === '') {
                throw ValidationException::withMessages([
                    'batch_number' => 'A batch number is required for this item.',
                ]);
            }

            if ($product->has_expiry && blank($expiryDate)) {
                throw ValidationException::withMessages([
                    'expiry_date' => 'An expiry date is required for this item.',
                ]);
            }

            return InventoryBatch::query()->firstOrCreate(
                [
                    'tenant_id' => tenant('id'),
                    'product_id' => $product->id,
                    'location_id' => $attributes['location_id'] ?? null,
                    'batch_number' => $batchNumber,
                ],
                [
                    'expiry_date' => $expiryDate,
                    'manufactured_at' => $attributes['manufactured_at'] ?? null,
                    'received_at' => $attributes['received_at'] ?? ($attributes['movement_date'] ?? now()),
                    'cost_price' => $attributes['unit_cost'] ?? null,
                    'status' => InventoryBatchStatus::Active,
                    'notes' => $attributes['notes'] ?? null,
                    'quantity_on_hand' => 0,
                ]
            );
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function validateBatchUsage(Product $product, InventoryBatch $batch, InventoryMovementType $movementType, InventoryDirection $direction, float $baseQuantity, array $attributes): void
    {
        if ((int) $batch->product_id !== $product->id) {
            throw ValidationException::withMessages([
                'batch_id' => 'The selected batch does not belong to the selected product.',
            ]);
        }

        if (isset($attributes['location_id']) && $attributes['location_id'] !== null && (int) $batch->location_id !== (int) $attributes['location_id']) {
            throw ValidationException::withMessages([
                'location_id' => 'The selected batch does not belong to the selected stock location.',
            ]);
        }

        if ($direction === InventoryDirection::Out && ! $product->allow_negative_stock && ((float) $batch->quantity_on_hand - $baseQuantity) < 0) {
            throw ValidationException::withMessages([
                'batch_id' => sprintf('Batch %s does not have enough stock.', $batch->batch_number),
            ]);
        }

        if ($movementType === InventoryMovementType::SaleIssue && $batch->isExpired()) {
            throw ValidationException::withMessages([
                'batch_id' => sprintf('Batch %s is expired and cannot be sold.', $batch->batch_number),
            ]);
        }
    }

    private function resolveBatchStatus(InventoryBatch $batch, float $balance): InventoryBatchStatus
    {
        if ($batch->expiry_date !== null && $batch->expiry_date->lt(today())) {
            return InventoryBatchStatus::Expired;
        }

        return $balance <= 0 ? InventoryBatchStatus::Depleted : InventoryBatchStatus::Active;
    }
}
