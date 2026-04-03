<?php

declare(strict_types=1);

namespace App\Livewire\Purchasing\Returns;

use App\Actions\Purchasing\CreatePurchaseReturnAction;
use App\Models\InventoryStock;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseReturn;
use App\Models\StockLocation;
use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

final class CreatePage extends Component
{
    #[Url(as: 'receipt')]
    public ?int $purchase_receipt_id = null;

    public string $return_number = '';
    public string $supplier_id = '';
    public string $stock_location_id = '';
    public string $return_date = '';
    public string $notes = '';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $items = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('purchase-returns.create'), 403);

        $this->return_number = $this->generateReturnNumber();
        $this->return_date = now()->toDateString();
        $this->items = [$this->blankItem()];

        if ($this->purchase_receipt_id !== null) {
            $this->prefillFromReceipt();
        }
    }

    protected function rules(): array
    {
        $tenant = tenant();

        return [
            'return_number' => ['required', 'string', 'max:255', $tenant->unique('purchase_returns', 'return_number')],
            'supplier_id' => ['required', $tenant->exists('suppliers', 'id')],
            'stock_location_id' => ['required', $tenant->exists('stock_locations', 'id')],
            'return_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_item_id' => ['required', $tenant->exists('inventory_items', 'id')],
            'items.*.inventory_stock_id' => ['required', $tenant->exists('inventory_stocks', 'id')],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ];
    }

    public function addItem(): void
    {
        $this->items[] = $this->blankItem();
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);

        if ($this->items === []) {
            $this->items[] = $this->blankItem();
        }
    }

    public function updatedItems($value, ?string $key = null): void
    {
        if ($key === null) {
            return;
        }

        [$index, $field] = explode('.', $key, 2);
        $index = (int) $index;

        if ($field !== 'inventory_stock_id') {
            return;
        }

        $stock = InventoryStock::query()->with('product')->find((int) $value);

        if ($stock === null) {
            return;
        }

        $this->items[$index]['inventory_item_id'] = (string) $stock->inventory_item_id;
        $this->items[$index]['unit_cost'] = $stock->unit_cost === null ? '' : (string) ((float) $stock->unit_cost);
    }

    public function save(CreatePurchaseReturnAction $createPurchaseReturn): mixed
    {
        abort_unless(auth()->user()?->can('purchase-returns.create'), 403);

        $this->validate();

        $return = $createPurchaseReturn->handle([
            'return_number' => trim($this->return_number),
            'supplier_id' => (int) $this->supplier_id,
            'purchase_receipt_id' => $this->purchase_receipt_id,
            'stock_location_id' => (int) $this->stock_location_id,
            'return_date' => $this->return_date,
            'notes' => $this->notes === '' ? null : trim($this->notes),
        ], collect($this->items)->map(function (array $item): array {
            $quantity = (float) $item['quantity'];
            $unitCost = (float) $item['unit_cost'];

            return [
                'inventory_item_id' => (int) $item['inventory_item_id'],
                'inventory_stock_id' => (int) $item['inventory_stock_id'],
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'line_total' => round($quantity * $unitCost, 2),
                'notes' => trim((string) ($item['notes'] ?? '')),
            ];
        })->all());

        session()->flash('success', 'Purchase return recorded successfully.');

        return $this->redirectRoute('purchase-returns.show', ['purchaseReturn' => $return->id]);
    }

    public function render(): View
    {
        return view('livewire.purchasing.returns.create-page', [
            'selectedReceipt' => $this->selectedReceipt(),
            'locations' => StockLocation::query()->active()->ordered()->get(),
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
            'stocks' => $this->availableStocks(),
            'total' => collect($this->items)->sum(fn (array $item): float => ((float) ($item['quantity'] ?: 0)) * ((float) ($item['unit_cost'] ?: 0))),
        ]);
    }

    private function selectedReceipt(): ?PurchaseReceipt
    {
        if ($this->purchase_receipt_id === null) {
            return null;
        }

        return PurchaseReceipt::query()
            ->with(['supplier', 'stockLocation', 'items.product'])
            ->find($this->purchase_receipt_id);
    }

    private function prefillFromReceipt(): void
    {
        $receipt = $this->selectedReceipt();

        if ($receipt === null) {
            $this->purchase_receipt_id = null;

            return;
        }

        $this->supplier_id = (string) $receipt->supplier_id;
        $this->stock_location_id = (string) $receipt->stock_location_id;
        $this->notes = $receipt->notes ?? '';
        $this->items = $receipt->items->map(function ($item): array {
            $stock = InventoryStock::query()
                ->where('inventory_item_id', $item->inventory_item_id)
                ->where('location_id', $this->stock_location_id)
                ->when($item->batch_number !== null, fn ($query) => $query->where('batch_number', $item->batch_number))
                ->first();

            return [
                'inventory_item_id' => (string) $item->inventory_item_id,
                'inventory_stock_id' => $stock?->id ? (string) $stock->id : '',
                'quantity' => '',
                'unit_cost' => $item->unit_cost === null ? '' : (string) ((float) $item->unit_cost),
                'notes' => $item->notes ?? '',
            ];
        })->values()->all();

        if ($this->items === []) {
            $this->items = [$this->blankItem()];
        }
    }

    private function availableStocks()
    {
        if ($this->stock_location_id === '') {
            return collect();
        }

        return InventoryStock::query()
            ->with('product')
            ->where('location_id', (int) $this->stock_location_id)
            ->available()
            ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expiry_date')
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function blankItem(): array
    {
        return [
            'inventory_item_id' => '',
            'inventory_stock_id' => '',
            'quantity' => '',
            'unit_cost' => '',
            'notes' => '',
        ];
    }

    private function generateReturnNumber(): string
    {
        $count = PurchaseReturn::query()->count() + 1;

        return sprintf('PRN-%s-%03d', now()->format('Y'), $count);
    }
}

