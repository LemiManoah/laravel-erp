<?php

declare(strict_types=1);

namespace App\Livewire\Inventory\Receipts;

use App\Actions\Inventory\RecordInventoryMovementAction;
use App\Enums\InventoryMovementType;
use App\Models\InventoryItem;
use App\Models\StockLocation;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

final class CreatePage extends Component
{
    public string $receipt_type = 'opening_stock';

    public string $inventory_item_id = '';

    public string $location_id = '';

    public string $quantity = '';

    public string $unit_cost = '';

    public string $movement_date = '';

    public string $batch_number = '';

    public string $expiry_date = '';

    public string $notes = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('inventory-movements.create'), 403);

        $this->movement_date = now()->format('Y-m-d\TH:i');
        $this->receipt_type = InventoryMovementType::OpeningStock->value;
    }

    protected function rules(): array
    {
        $tenant = tenant();
        $selectedProduct = $this->selectedProduct();

        return [
            'receipt_type' => ['required', Rule::in($this->allowedReceiptTypes()->pluck('value')->all())],
            'inventory_item_id' => ['required', $tenant->exists('inventory_items', 'id')],
            'location_id' => ['required', $tenant->exists('stock_locations', 'id')],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'movement_date' => ['required', 'date'],
            'batch_number' => ['nullable', Rule::requiredIf($selectedProduct?->has_expiry), 'string', 'max:255'],
            'expiry_date' => ['nullable', Rule::requiredIf($selectedProduct?->has_expiry), 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function save(RecordInventoryMovementAction $recordInventoryMovement): mixed
    {
        abort_unless(auth()->user()?->can('inventory-movements.create'), 403);

        $this->validate();

        $product = InventoryItem::query()->findOrFail((int) $this->inventory_item_id);
        $movementType = InventoryMovementType::from($this->receipt_type);

        $recordInventoryMovement->handle($product, $movementType, (float) $this->quantity, [
            'location_id' => (int) $this->location_id,
            'batch_number' => $this->batch_number !== '' ? trim($this->batch_number) : null,
            'expiry_date' => $this->expiry_date !== '' ? $this->expiry_date : null,
            'received_at' => substr($this->movement_date, 0, 10),
            'movement_date' => $this->movement_date,
            'unit_cost' => $this->unit_cost !== '' ? (float) $this->unit_cost : null,
            'notes' => $this->notes !== '' ? trim($this->notes) : null,
        ]);

        session()->flash('success', 'Stock receipt recorded successfully.');

        return $this->redirectRoute('inventory.stocks.index');
    }

    public function render(): View
    {
        return view('livewire.inventory.receipts.create-page', [
            'products' => InventoryItem::query()->stockTracked()->active()->orderBy('name')->get(),
            'locations' => StockLocation::query()->active()->ordered()->get(),
            'receiptTypes' => $this->allowedReceiptTypes(),
            'selectedProduct' => $this->selectedProduct(),
        ]);
    }

    private function selectedProduct(): ?InventoryItem
    {
        if (! filled($this->inventory_item_id)) {
            return null;
        }

        return InventoryItem::query()->find((int) $this->inventory_item_id);
    }

    private function allowedReceiptTypes()
    {
        return collect([
            InventoryMovementType::OpeningStock,
            InventoryMovementType::PurchaseReceipt,
            InventoryMovementType::SalesReturn,
            InventoryMovementType::AdjustmentGain,
            InventoryMovementType::Harvest,
            InventoryMovementType::ProductionOutput,
        ]);
    }
}

