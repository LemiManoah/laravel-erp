<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Enums\InventoryBatchStatus;
use App\Enums\InventoryMovementType;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\StockLocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class TransferInventoryAction
{
    public function __construct(
        private RecordInventoryMovementAction $recordInventoryMovement,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     * @return array{out: \App\Models\InventoryMovement, in: \App\Models\InventoryMovement}
     */
    public function handle(Product $product, StockLocation $fromLocation, StockLocation $toLocation, float $quantity, array $attributes = []): array
    {
        if ($fromLocation->is($toLocation)) {
            throw ValidationException::withMessages([
                'to_location_id' => 'Choose a different destination location for the transfer.',
            ]);
        }

        return DB::transaction(function () use ($product, $fromLocation, $toLocation, $quantity, $attributes): array {
            $batch = null;
            $destinationBatch = null;

            if (isset($attributes['batch_id']) && filled($attributes['batch_id'])) {
                $batch = InventoryBatch::query()->lockForUpdate()->findOrFail((int) $attributes['batch_id']);

                if ((int) $batch->location_id !== $fromLocation->id) {
                    throw ValidationException::withMessages([
                        'batch_id' => 'The selected batch is not stored in the source location.',
                    ]);
                }

                $destinationBatch = InventoryBatch::query()->firstOrCreate(
                    [
                        'tenant_id' => tenant('id'),
                        'product_id' => $product->id,
                        'location_id' => $toLocation->id,
                        'batch_number' => $batch->batch_number,
                    ],
                    [
                        'expiry_date' => $batch->expiry_date,
                        'manufactured_at' => $batch->manufactured_at,
                        'received_at' => $batch->received_at,
                        'cost_price' => $batch->cost_price,
                        'status' => InventoryBatchStatus::Active,
                        'quantity_on_hand' => 0,
                        'notes' => $attributes['notes'] ?? null,
                    ]
                );
            }

            $out = $this->recordInventoryMovement->handle($product, InventoryMovementType::TransferOut, $quantity, [
                ...$attributes,
                'location_id' => $fromLocation->id,
                'batch' => $batch,
            ]);

            $in = $this->recordInventoryMovement->handle($product, InventoryMovementType::TransferIn, $quantity, [
                ...$attributes,
                'location_id' => $toLocation->id,
                'batch' => $destinationBatch,
                'batch_number' => $destinationBatch?->batch_number,
                'expiry_date' => $destinationBatch?->expiry_date,
                'manufactured_at' => $destinationBatch?->manufactured_at,
                'received_at' => $attributes['movement_date'] ?? now(),
            ]);

            return compact('out', 'in');
        });
    }
}
