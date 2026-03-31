<?php

declare(strict_types=1);

namespace App\Livewire\Inventory\Movements;

use App\Actions\Inventory\RecordInventoryMovementAction;
use App\Enums\InventoryMovementType;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\StockLocation;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

final class CreatePage extends Component
{
    public string $movement_type = 'purchase_receipt';

    public string $product_id = '';

    public string $location_id = '';

    public string $batch_id = '';

    public string $quantity = '';

    public string $unit_cost = '';

    public string $movement_date = '';

    public string $batch_number = '';

    public string $expiry_date = '';

    public string $manufactured_at = '';

    public string $notes = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('inventory-movements.create'), 403);

        $this->movement_date = now()->format('Y-m-d\TH:i');
    }

    protected function rules(): array
    {
        $tenant = tenant();
        $product = $this->selectedProduct();
        $movementType = $this->selectedMovementType();
        $requiresBatchOnIn = $movementType->direction()->value === 'in' && $product !== null && ($product->requires_batch_tracking || $product->has_expiry);
        $requiresExistingBatch = $movementType->direction()->value === 'out' && $product !== null && ($product->requires_batch_tracking || $product->has_expiry);

        return [
            'movement_type' => ['required', Rule::enum(InventoryMovementType::class), Rule::notIn([InventoryMovementType::SaleIssue->value, InventoryMovementType::TransferIn->value, InventoryMovementType::TransferOut->value])],
            'product_id' => ['required', $tenant->exists('products', 'id')],
            'location_id' => ['required', $tenant->exists('stock_locations', 'id')],
            'batch_id' => ['nullable', Rule::requiredIf($requiresExistingBatch), $tenant->exists('inventory_batches', 'id')],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'movement_date' => ['required', 'date'],
            'batch_number' => ['nullable', Rule::requiredIf($requiresBatchOnIn), 'string', 'max:255'],
            'expiry_date' => ['nullable', Rule::requiredIf($product?->has_expiry && $requiresBatchOnIn), 'date'],
            'manufactured_at' => ['nullable', 'date', 'before_or_equal:movement_date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function save(RecordInventoryMovementAction $recordInventoryMovement): mixed
    {
        abort_unless(auth()->user()?->can('inventory-movements.create'), 403);

        $this->validate();

        $product = Product::query()->findOrFail((int) $this->product_id);
        $movementType = $this->selectedMovementType();

        $recordInventoryMovement->handle($product, $movementType, (float) $this->quantity, [
            'location_id' => (int) $this->location_id,
            'batch_id' => filled($this->batch_id) ? (int) $this->batch_id : null,
            'batch_number' => $this->batch_number !== '' ? trim($this->batch_number) : null,
            'expiry_date' => $this->expiry_date !== '' ? $this->expiry_date : null,
            'manufactured_at' => $this->manufactured_at !== '' ? $this->manufactured_at : null,
            'received_at' => substr($this->movement_date, 0, 10),
            'movement_date' => $this->movement_date,
            'unit_cost' => $this->unit_cost !== '' ? (float) $this->unit_cost : null,
            'notes' => $this->notes !== '' ? trim($this->notes) : null,
        ]);

        session()->flash('success', 'Inventory movement recorded successfully.');

        return $this->redirectRoute('inventory.movements.index');
    }

    public function render(): View
    {
        return view('livewire.inventory.movements.create-page', [
            'products' => Product::query()->stockTracked()->active()->orderBy('name')->get(),
            'locations' => StockLocation::query()->active()->ordered()->get(),
            'batches' => $this->availableBatches(),
            'movementTypes' => collect(InventoryMovementType::cases())
                ->reject(fn (InventoryMovementType $type): bool => $type->isTransfer() || $type === InventoryMovementType::SaleIssue)
                ->values(),
            'selectedProduct' => $this->selectedProduct(),
            'selectedMovementType' => $this->selectedMovementType(),
        ]);
    }

    private function selectedProduct(): ?Product
    {
        if (! filled($this->product_id)) {
            return null;
        }

        return Product::query()->find((int) $this->product_id);
    }

    private function availableBatches()
    {
        if (! filled($this->product_id) || ! filled($this->location_id)) {
            return collect();
        }

        return InventoryBatch::query()
            ->where('product_id', (int) $this->product_id)
            ->where('location_id', (int) $this->location_id)
            ->available()
            ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expiry_date')
            ->get();
    }

    private function selectedMovementType(): InventoryMovementType
    {
        return InventoryMovementType::tryFrom($this->movement_type) ?? InventoryMovementType::PurchaseReceipt;
    }
}
