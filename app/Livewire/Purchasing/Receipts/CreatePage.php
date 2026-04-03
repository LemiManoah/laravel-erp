<?php

declare(strict_types=1);

namespace App\Livewire\Purchasing\Receipts;

use App\Actions\Purchasing\CreatePurchaseReceiptAction;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReceipt;
use App\Models\StockLocation;
use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

final class CreatePage extends Component
{
    #[Url(as: 'order')]
    public ?int $purchase_order_id = null;

    public string $receipt_number = '';
    public string $supplier_id = '';
    public string $stock_location_id = '';
    public string $receipt_date = '';
    public string $notes = '';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $items = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('purchase-receipts.create'), 403);

        $this->receipt_number = $this->generateReceiptNumber();
        $this->receipt_date = now()->toDateString();
        $this->items = [$this->blankItem()];

        if ($this->purchase_order_id !== null) {
            $this->prefillFromPurchaseOrder();
        }
    }

    protected function rules(): array
    {
        $tenant = tenant();

        return [
            'receipt_number' => ['required', 'string', 'max:255', $tenant->unique('purchase_receipts', 'receipt_number')],
            'supplier_id' => ['required', $tenant->exists('suppliers', 'id')],
            'stock_location_id' => ['required', $tenant->exists('stock_locations', 'id')],
            'receipt_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_item_id' => ['required', $tenant->exists('inventory_items', 'id')],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.batch_number' => ['nullable', 'string', 'max:255'],
            'items.*.expiry_date' => ['nullable', 'date'],
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

        if ($field !== 'inventory_item_id') {
            return;
        }

        $product = InventoryItem::query()->with('defaultPrice')->find((int) $value);

        if ($product !== null && blank($this->items[$index]['unit_cost'])) {
            $this->items[$index]['unit_cost'] = $product->purchase_price ?? '';
        }

        if ($product !== null && ! $product->has_expiry) {
            $this->items[$index]['batch_number'] = '';
            $this->items[$index]['expiry_date'] = '';
        }
    }

    public function save(CreatePurchaseReceiptAction $createPurchaseReceipt): mixed
    {
        abort_unless(auth()->user()?->can('purchase-receipts.create'), 403);

        $this->validate();
        $this->validateInventoryItems();

        $receipt = $createPurchaseReceipt->handle([
            'receipt_number' => trim($this->receipt_number),
            'supplier_id' => (int) $this->supplier_id,
            'purchase_order_id' => $this->purchase_order_id,
            'stock_location_id' => (int) $this->stock_location_id,
            'receipt_date' => $this->receipt_date,
            'notes' => $this->notes === '' ? null : trim($this->notes),
        ], collect($this->items)->map(function (array $item): array {
            $quantity = (float) $item['quantity'];
            $unitCost = (float) $item['unit_cost'];

            return [
                'inventory_item_id' => (int) $item['inventory_item_id'],
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'line_total' => round($quantity * $unitCost, 2),
                'batch_number' => trim((string) ($item['batch_number'] ?? '')),
                'expiry_date' => $item['expiry_date'] ?? '',
                'notes' => trim((string) ($item['notes'] ?? '')),
            ];
        })->all());

        session()->flash('success', 'Purchase receipt posted successfully.');

        return $this->redirectRoute('purchase-receipts.show', ['receipt' => $receipt->id]);
    }

    public function render(): View
    {
        return view('livewire.purchasing.receipts.create-page', [
            'selectedOrder' => $this->selectedOrder(),
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
            'locations' => StockLocation::query()->active()->ordered()->get(),
            'products' => InventoryItem::query()->stockTracked()->purchasable()->active()->with('defaultPrice')->orderBy('name')->get(),
            'total' => collect($this->items)->sum(fn (array $item): float => ((float) ($item['quantity'] ?: 0)) * ((float) ($item['unit_cost'] ?: 0))),
        ]);
    }

    private function validateInventoryItems(): void
    {
        $messages = [];

        foreach ($this->items as $index => $item) {
            $product = InventoryItem::query()->find((int) ($item['inventory_item_id'] ?? 0));

            if ($product === null) {
                continue;
            }

            if (! $product->tracks_inventory) {
                $messages["items.$index.inventory_item_id"] = 'Selected inventory item does not track inventory.';
            }

            if ($product->has_expiry) {
                if (blank($item['batch_number'] ?? null)) {
                    $messages["items.$index.batch_number"] = 'Batch number is required for expiring items.';
                }

                if (blank($item['expiry_date'] ?? null)) {
                    $messages["items.$index.expiry_date"] = 'Expiry date is required for expiring items.';
                }
            }
        }

        if ($messages !== []) {
            throw \Illuminate\Validation\ValidationException::withMessages($messages);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function blankItem(): array
    {
        return [
            'inventory_item_id' => '',
            'quantity' => '',
            'unit_cost' => '',
            'batch_number' => '',
            'expiry_date' => '',
            'notes' => '',
        ];
    }

    private function generateReceiptNumber(): string
    {
        $count = PurchaseReceipt::query()->count() + 1;

        return sprintf('PRC-%s-%03d', now()->format('Y'), $count);
    }

    private function selectedOrder(): ?PurchaseOrder
    {
        if ($this->purchase_order_id === null) {
            return null;
        }

        return PurchaseOrder::query()
            ->with(['supplier', 'stockLocation', 'items.product.defaultPrice'])
            ->find($this->purchase_order_id);
    }

    private function prefillFromPurchaseOrder(): void
    {
        $order = $this->selectedOrder();

        if ($order === null) {
            $this->purchase_order_id = null;

            return;
        }

        $this->supplier_id = (string) $order->supplier_id;
        $this->stock_location_id = (string) $order->stock_location_id;
        $this->notes = $order->notes ?? '';
        $this->items = $order->items->map(function ($item): array {
            return [
                'inventory_item_id' => (string) $item->inventory_item_id,
                'quantity' => (string) ((float) $item->quantity),
                'unit_cost' => (string) ((float) $item->unit_cost),
                'batch_number' => '',
                'expiry_date' => '',
                'notes' => $item->notes ?? '',
            ];
        })->values()->all();

        if ($this->items === []) {
            $this->items = [$this->blankItem()];
        }
    }
}

