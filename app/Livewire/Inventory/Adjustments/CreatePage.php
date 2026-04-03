<?php

declare(strict_types=1);

namespace App\Livewire\Inventory\Adjustments;

use App\Actions\Inventory\RecordInventoryMovementAction;
use App\Enums\InventoryMovementType;
use App\Models\InventoryStock;
use App\Models\InventoryItem;
use App\Models\StockLocation;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

final class CreatePage extends Component
{
    public string $adjustment_type = 'adjustment_gain';

    public string $inventory_item_id = '';

    public string $location_id = '';

    public string $inventory_stock_id = '';

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
        $this->adjustment_type = InventoryMovementType::AdjustmentGain->value;
    }

    protected function rules(): array
    {
        $tenant = tenant();
        $product = $this->selectedProduct();
        $movementType = $this->selectedMovementType();
        $isOut = $movementType->direction()->value === 'out';
        $requiresExistingStock = $isOut;
        $requiresNewExpiryDetails = ! $isOut && $product?->has_expiry;

        return [
            'adjustment_type' => ['required', Rule::in($this->allowedAdjustmentTypes()->pluck('value')->all())],
            'inventory_item_id' => ['required', $tenant->exists('inventory_items', 'id')],
            'location_id' => ['required', $tenant->exists('stock_locations', 'id')],
            'inventory_stock_id' => ['nullable', Rule::requiredIf($requiresExistingStock), $tenant->exists('inventory_stocks', 'id')],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'movement_date' => ['required', 'date'],
            'batch_number' => ['nullable', Rule::requiredIf($requiresNewExpiryDetails), 'string', 'max:255'],
            'expiry_date' => ['nullable', Rule::requiredIf($requiresNewExpiryDetails), 'date'],
            'notes' => ['required', 'string', 'max:1000'],
        ];
    }

    public function save(RecordInventoryMovementAction $recordInventoryMovement): mixed
    {
        abort_unless(auth()->user()?->can('inventory-movements.create'), 403);

        $this->validate();

        $product = InventoryItem::query()->findOrFail((int) $this->inventory_item_id);
        $movementType = $this->selectedMovementType();

        $attributes = [
            'location_id' => (int) $this->location_id,
            'inventory_stock_id' => filled($this->inventory_stock_id) ? (int) $this->inventory_stock_id : null,
            'movement_date' => $this->movement_date,
            'unit_cost' => $this->unit_cost !== '' ? (float) $this->unit_cost : null,
            'notes' => trim($this->notes),
        ];

        if ($movementType->direction()->value === 'in') {
            $attributes['batch_number'] = $this->batch_number !== '' ? trim($this->batch_number) : null;
            $attributes['expiry_date'] = $this->expiry_date !== '' ? $this->expiry_date : null;
            $attributes['received_at'] = substr($this->movement_date, 0, 10);
        }

        $recordInventoryMovement->handle($product, $movementType, (float) $this->quantity, $attributes);

        session()->flash('success', 'Stock adjustment recorded successfully.');

        return $this->redirectRoute('inventory.movements.index');
    }

    public function render(): View
    {
        return view('livewire.inventory.adjustments.create-page', [
            'products' => InventoryItem::query()->stockTracked()->active()->orderBy('name')->get(),
            'locations' => StockLocation::query()->active()->ordered()->get(),
            'stocks' => $this->availableStocks(),
            'adjustmentTypes' => $this->allowedAdjustmentTypes(),
            'selectedProduct' => $this->selectedProduct(),
            'selectedMovementType' => $this->selectedMovementType(),
        ]);
    }

    private function selectedProduct(): ?InventoryItem
    {
        if (! filled($this->inventory_item_id)) {
            return null;
        }

        return InventoryItem::query()->find((int) $this->inventory_item_id);
    }

    private function selectedMovementType(): InventoryMovementType
    {
        return InventoryMovementType::tryFrom($this->adjustment_type) ?? InventoryMovementType::AdjustmentGain;
    }

    private function allowedAdjustmentTypes()
    {
        return collect([
            InventoryMovementType::AdjustmentGain,
            InventoryMovementType::AdjustmentLoss,
            InventoryMovementType::Damage,
            InventoryMovementType::Wastage,
            InventoryMovementType::InternalConsumption,
            InventoryMovementType::PurchaseReturn,
        ]);
    }

    private function availableStocks()
    {
        if (! filled($this->inventory_item_id) || ! filled($this->location_id)) {
            return collect();
        }

        return InventoryStock::query()
            ->where('inventory_item_id', (int) $this->inventory_item_id)
            ->where('location_id', (int) $this->location_id)
            ->available()
            ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expiry_date')
            ->get();
    }
}

