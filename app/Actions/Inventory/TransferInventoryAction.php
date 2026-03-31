<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Enums\InventoryMovementType;
use App\Models\InventoryStock;
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
            $stock = null;
            $destinationStock = null;

            if (isset($attributes['inventory_stock_id']) && filled($attributes['inventory_stock_id'])) {
                $stock = InventoryStock::query()->lockForUpdate()->findOrFail((int) $attributes['inventory_stock_id']);

                if ((int) $stock->location_id !== $fromLocation->id) {
                    throw ValidationException::withMessages([
                        'inventory_stock_id' => 'The selected stock record is not stored in the source location.',
                    ]);
                }

                $destinationStock = InventoryStock::query()->firstOrCreate(
                    [
                        'tenant_id' => tenant('id'),
                        'product_id' => $product->id,
                        'location_id' => $toLocation->id,
                        'batch_number' => $stock->batch_number,
                    ],
                    [
                        'expiry_date' => $stock->expiry_date,
                        'received_at' => $stock->received_at,
                        'unit_cost' => $stock->unit_cost,
                        'quantity_on_hand' => 0,
                        'notes' => $attributes['notes'] ?? null,
                    ]
                );
            }

            $out = $this->recordInventoryMovement->handle($product, InventoryMovementType::TransferOut, $quantity, [
                ...$attributes,
                'location_id' => $fromLocation->id,
                'inventory_stock' => $stock,
            ]);

            $in = $this->recordInventoryMovement->handle($product, InventoryMovementType::TransferIn, $quantity, [
                ...$attributes,
                'location_id' => $toLocation->id,
                'inventory_stock' => $destinationStock,
                'batch_number' => $destinationStock?->batch_number,
                'expiry_date' => $destinationStock?->expiry_date,
                'received_at' => $attributes['movement_date'] ?? now(),
            ]);

            return compact('out', 'in');
        });
    }
}
