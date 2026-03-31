<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Enums\InventoryMovementType;
use App\Models\InventoryBatch;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\StockLocation;
use Illuminate\Validation\ValidationException;

final readonly class IssueInvoiceInventoryAction
{
    public function __construct(
        private RecordInventoryMovementAction $recordInventoryMovement,
    ) {}

    public function handle(Invoice $invoice): void
    {
        $invoice->loadMissing(['items.product', 'stockLocation']);

        if ($invoice->items->every(fn ($item): bool => $item->product === null || ! $item->product->tracks_inventory)) {
            return;
        }

        $location = $this->resolveLocation($invoice);

        foreach ($invoice->items as $item) {
            $product = $item->product;

            if ($product === null || ! $product->tracks_inventory) {
                continue;
            }

            $quantity = (float) $item->quantity;

            if ($product->requires_batch_tracking || $product->has_expiry) {
                $this->issueFromBatches($invoice, $product, $location, $quantity);

                continue;
            }

            $this->recordInventoryMovement->handle($product, InventoryMovementType::SaleIssue, $quantity, [
                'location_id' => $location->id,
                'reference_type' => 'invoice',
                'reference_id' => $invoice->id,
                'movement_date' => $invoice->issued_at ?? now(),
                'notes' => sprintf('Issued from invoice %s', $invoice->invoice_number),
            ]);
        }
    }

    private function resolveLocation(Invoice $invoice): StockLocation
    {
        if ($invoice->stockLocation !== null) {
            return $invoice->stockLocation;
        }

        $location = StockLocation::query()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->first();

        if ($location === null) {
            throw ValidationException::withMessages([
                'stock_location_id' => 'A stock location is required before issuing inventory items on an invoice.',
            ]);
        }

        $invoice->forceFill(['stock_location_id' => $location->id])->save();

        return $location;
    }

    private function issueFromBatches(Invoice $invoice, Product $product, StockLocation $location, float $quantity): void
    {
        $remaining = $quantity;
        $batches = InventoryBatch::query()
            ->where('product_id', $product->id)
            ->where('location_id', $location->id)
            ->available()
            ->where(function ($query): void {
                $query->whereNull('expiry_date')
                    ->orWhereDate('expiry_date', '>=', now()->toDateString());
            })
            ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expiry_date')
            ->orderBy('received_at')
            ->lockForUpdate()
            ->get();

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $available = (float) $batch->quantity_on_hand;

            if ($available <= 0) {
                continue;
            }

            $issueQuantity = min($remaining, $available);

            $this->recordInventoryMovement->handle($product, InventoryMovementType::SaleIssue, $issueQuantity, [
                'location_id' => $location->id,
                'batch' => $batch,
                'reference_type' => 'invoice',
                'reference_id' => $invoice->id,
                'movement_date' => $invoice->issued_at ?? now(),
                'notes' => sprintf('Issued from invoice %s', $invoice->invoice_number),
            ]);

            $remaining -= $issueQuantity;
        }

        if ($remaining > 0) {
            throw ValidationException::withMessages([
                'items' => sprintf('Not enough batch stock is available for %s in %s.', $product->name, $location->name),
            ]);
        }
    }
}
