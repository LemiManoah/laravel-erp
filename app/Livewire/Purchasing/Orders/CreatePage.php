<?php

declare(strict_types=1);

namespace App\Livewire\Purchasing\Orders;

use App\Actions\Purchasing\CreatePurchaseOrderAction;
use App\Enums\PurchaseOrderStatus;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockLocation;
use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

final class CreatePage extends Component
{
    public string $order_number = '';
    public string $supplier_id = '';
    public string $stock_location_id = '';
    public string $order_date = '';
    public string $expected_date = '';
    public string $status = '';
    public string $notes = '';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $items = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('purchase-orders.create'), 403);

        $this->order_number = $this->generateOrderNumber();
        $this->order_date = now()->toDateString();
        $this->status = PurchaseOrderStatus::Ordered->value;
        $this->items = [$this->blankItem()];
    }

    protected function rules(): array
    {
        $tenant = tenant();

        return [
            'order_number' => ['required', 'string', 'max:255', $tenant->unique('purchase_orders', 'order_number')],
            'supplier_id' => ['required', $tenant->exists('suppliers', 'id')],
            'stock_location_id' => ['required', $tenant->exists('stock_locations', 'id')],
            'order_date' => ['required', 'date'],
            'expected_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'status' => ['required', Rule::in(array_column(PurchaseOrderStatus::cases(), 'value'))],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', $tenant->exists('products', 'id')],
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

        if ($field !== 'product_id') {
            return;
        }

        $product = Product::query()->with('defaultPrice')->find((int) $value);

        if ($product !== null && blank($this->items[$index]['unit_cost'])) {
            $this->items[$index]['unit_cost'] = $product->buying_price ?? '';
        }
    }

    public function save(CreatePurchaseOrderAction $createPurchaseOrder): mixed
    {
        abort_unless(auth()->user()?->can('purchase-orders.create'), 403);

        $this->validate();
        $this->validatePurchasableItems();

        $order = $createPurchaseOrder->handle([
            'order_number' => trim($this->order_number),
            'supplier_id' => (int) $this->supplier_id,
            'stock_location_id' => (int) $this->stock_location_id,
            'order_date' => $this->order_date,
            'expected_date' => $this->expected_date === '' ? null : $this->expected_date,
            'status' => PurchaseOrderStatus::from($this->status),
            'notes' => $this->notes === '' ? null : trim($this->notes),
        ], collect($this->items)->map(function (array $item): array {
            $quantity = (float) $item['quantity'];
            $unitCost = (float) $item['unit_cost'];

            return [
                'product_id' => (int) $item['product_id'],
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'line_total' => round($quantity * $unitCost, 2),
                'notes' => trim((string) ($item['notes'] ?? '')),
            ];
        })->all());

        session()->flash('success', 'Purchase order saved successfully.');

        return $this->redirectRoute('purchase-orders.show', ['order' => $order->id]);
    }

    public function render(): View
    {
        return view('livewire.purchasing.orders.create-page', [
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
            'locations' => StockLocation::query()->active()->ordered()->get(),
            'products' => Product::query()->purchasable()->active()->with('defaultPrice')->orderBy('name')->get(),
            'statuses' => PurchaseOrderStatus::cases(),
            'total' => collect($this->items)->sum(fn (array $item): float => ((float) ($item['quantity'] ?: 0)) * ((float) ($item['unit_cost'] ?: 0))),
        ]);
    }

    private function validatePurchasableItems(): void
    {
        $messages = [];

        foreach ($this->items as $index => $item) {
            $product = Product::query()->find((int) ($item['product_id'] ?? 0));

            if ($product !== null && ! $product->is_purchasable) {
                $messages["items.$index.product_id"] = 'Selected product is not marked as purchasable.';
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
            'product_id' => '',
            'quantity' => '',
            'unit_cost' => '',
            'notes' => '',
        ];
    }

    private function generateOrderNumber(): string
    {
        $count = PurchaseOrder::query()->count() + 1;

        return sprintf('PO-%s-%03d', now()->format('Y'), $count);
    }
}
