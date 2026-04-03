<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Enums\InventoryMovementType;
use App\Models\Invoice;

final readonly class ReverseInvoiceInventoryAction
{
    public function __construct(
        private RecordInventoryMovementAction $recordInventoryMovement,
    ) {}

    public function handle(Invoice $invoice): void
    {
        $issueMovements = $invoice->inventoryMovements()
            ->with(['inventoryItem', 'inventoryStock'])
            ->where('movement_type', InventoryMovementType::SaleIssue)
            ->get();

        foreach ($issueMovements as $movement) {
            if ($movement->inventoryItem === null) {
                continue;
            }

            $this->recordInventoryMovement->handle($movement->inventoryItem, InventoryMovementType::SalesReturn, (float) $movement->quantity, [
                'location_id' => $movement->location_id,
                'inventory_stock' => $movement->inventoryStock,
                'unit_id' => $movement->unit_id,
                'unit_conversion_rate' => $movement->unit_conversion_rate,
                'unit_cost' => $movement->unit_cost,
                'reference_type' => 'invoice',
                'reference_id' => $invoice->id,
                'movement_date' => now(),
                'notes' => sprintf('Inventory restored after cancelling invoice %s', $invoice->invoice_number),
            ]);
        }
    }
}

