<?php

declare(strict_types=1);

namespace App\Livewire\Orders;

use App\Actions\Order\CreateOrderAction;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class CreatePage extends Component
{
    public string $customer_id = '';
    public string $currency_id = '';
    public string $order_date = '';
    public string $promised_delivery_date = '';
    public string $priority = 'medium';
    public string $notes = '';
    public array $items = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('orders.create'), 403);

        $this->order_date = now()->toDateString();

        $defaultCurrency = Currency::active()->where('is_default', true)->first()
            ?? Currency::active()->first();
        if ($defaultCurrency) {
            $this->currency_id = (string) $defaultCurrency->id;
        }

        $this->items = [$this->blankItem()];
    }

    protected function rules(): array
    {
        $tenant = tenant();

        return [
            'customer_id' => ['required', 'integer', $tenant->exists('customers', 'id')],
            'currency_id' => ['required', 'integer', $tenant->exists('currencies', 'id')],
            'order_date' => ['required', 'date'],
            'promised_delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'priority' => ['required', 'string', 'in:low,medium,high,urgent'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'integer', $tenant->exists('products', 'id')],
            'items.*.garment_type' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.style_notes' => ['nullable', 'string'],
            'items.*.fabric_details' => ['nullable', 'string'],
        ];
    }

    public function updatedItems($value, ?string $key = null): void
    {
        if ($key === null) {
            return;
        }

        [$index, $field] = explode('.', $key, 2);

        if ($field === 'product_id' && $value !== '') {
            $product = Product::query()->find((int) $value);
            if ($product !== null) {
                $this->items[(int) $index]['garment_type'] = $product->name;
            }
        }
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

    public function save(CreateOrderAction $action): mixed
    {
        abort_unless(auth()->user()?->can('orders.create'), 403);

        $this->validate();

        $order = $action->handle([
            'customer_id' => (int) $this->customer_id,
            'currency_id' => (int) $this->currency_id,
            'order_date' => $this->order_date,
            'promised_delivery_date' => $this->promised_delivery_date !== '' ? $this->promised_delivery_date : null,
            'priority' => $this->priority,
            'notes' => $this->notes !== '' ? $this->notes : null,
            'items' => collect($this->items)->map(fn ($item): array => [
                'product_id' => isset($item['product_id']) && $item['product_id'] !== '' ? (int) $item['product_id'] : null,
                'garment_type' => $item['garment_type'],
                'quantity' => (int) $item['quantity'],
                'unit_price' => isset($item['unit_price']) && $item['unit_price'] !== '' ? (float) $item['unit_price'] : 0,
                'style_notes' => isset($item['style_notes']) && $item['style_notes'] !== '' ? $item['style_notes'] : null,
                'fabric_details' => isset($item['fabric_details']) && $item['fabric_details'] !== '' ? $item['fabric_details'] : null,
            ])->toArray(),
        ]);

        session()->flash('success', 'Order created successfully.');

        return $this->redirectRoute('orders.show', $order);
    }

    public function render(): View
    {
        return view('livewire.orders.create-page', [
            'customers' => Customer::query()->orderBy('full_name')->get(),
            'currencies' => Currency::active()->ordered()->get(),
            'products' => Product::query()->active()->orderBy('name')->get(),
        ]);
    }

    private function blankItem(): array
    {
        return [
            'product_id' => '',
            'garment_type' => '',
            'quantity' => 1,
            'unit_price' => '',
            'style_notes' => '',
            'fabric_details' => '',
        ];
    }
}
