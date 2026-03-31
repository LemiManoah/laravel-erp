<?php

declare(strict_types=1);

namespace App\Livewire\Inventory\Transfers;

use App\Actions\Inventory\TransferInventoryAction;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\StockLocation;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class CreatePage extends Component
{
    public string $product_id = '';

    public string $from_location_id = '';

    public string $to_location_id = '';

    public string $inventory_stock_id = '';

    public string $quantity = '';

    public string $movement_date = '';

    public string $notes = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('inventory-transfers.create'), 403);

        $this->movement_date = now()->format('Y-m-d\TH:i');
    }

    protected function rules(): array
    {
        $tenant = tenant();
        $product = $this->selectedProduct();
        $requiresStockSelection = $product?->has_expiry;

        return [
            'product_id' => ['required', $tenant->exists('products', 'id')],
            'from_location_id' => ['required', $tenant->exists('stock_locations', 'id')],
            'to_location_id' => ['required', 'different:from_location_id', $tenant->exists('stock_locations', 'id')],
            'inventory_stock_id' => ['nullable', \Illuminate\Validation\Rule::requiredIf($requiresStockSelection), $tenant->exists('inventory_stocks', 'id')],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'movement_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function save(TransferInventoryAction $transferInventory): mixed
    {
        abort_unless(auth()->user()?->can('inventory-transfers.create'), 403);

        $this->validate();

        $transferInventory->handle(
            Product::query()->findOrFail((int) $this->product_id),
            StockLocation::query()->findOrFail((int) $this->from_location_id),
            StockLocation::query()->findOrFail((int) $this->to_location_id),
            (float) $this->quantity,
            [
                'inventory_stock_id' => filled($this->inventory_stock_id) ? (int) $this->inventory_stock_id : null,
                'movement_date' => $this->movement_date,
                'notes' => $this->notes !== '' ? trim($this->notes) : null,
                'reference_type' => 'inventory_transfer',
            ],
        );

        session()->flash('success', 'Stock transfer recorded successfully.');

        return $this->redirectRoute('inventory.movements.index');
    }

    public function render(): View
    {
        return view('livewire.inventory.transfers.create-page', [
            'products' => Product::query()->stockTracked()->active()->orderBy('name')->get(),
            'locations' => StockLocation::query()->active()->ordered()->get(),
            'stocks' => $this->availableStocks(),
            'selectedProduct' => $this->selectedProduct(),
        ]);
    }

    private function selectedProduct(): ?Product
    {
        if (! filled($this->product_id)) {
            return null;
        }

        return Product::query()->find((int) $this->product_id);
    }

    private function availableStocks()
    {
        if (! filled($this->product_id) || ! filled($this->from_location_id)) {
            return collect();
        }

        return InventoryStock::query()
            ->where('product_id', (int) $this->product_id)
            ->where('location_id', (int) $this->from_location_id)
            ->available()
            ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expiry_date')
            ->get();
    }
}
