<?php

declare(strict_types=1);

namespace App\Livewire\Invoices;

use App\Actions\Invoice\UpdateInvoiceAction;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockLocation;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class EditPage extends Component
{
    #[Locked]
    public int $invoiceId;

    public string $customer_id = '';
    public string $order_id = '';
    public string $currency_id = '';
    public string $stock_location_id = '';
    public string $invoice_date = '';
    public string $due_date = '';
    public string $notes = '';
    public string $discount_amount = '0';
    public string $tax_amount = '0';
    public array $items = [];

    public function mount(Invoice $invoice): void
    {
        abort_unless(auth()->user()?->can('invoices.update'), 403);

        if ($invoice->status !== 'draft') {
            session()->flash('error', 'Only draft invoices can be edited.');
            $this->redirectRoute('invoices.show', $invoice);

            return;
        }

        $invoice->load('items');

        $this->invoiceId = $invoice->id;
        $this->customer_id = (string) $invoice->customer_id;
        $this->order_id = $invoice->order_id ? (string) $invoice->order_id : '';
        $this->currency_id = (string) $invoice->currency_id;
        $this->stock_location_id = $invoice->stock_location_id ? (string) $invoice->stock_location_id : '';
        $this->invoice_date = $invoice->invoice_date->format('Y-m-d');
        $this->due_date = $invoice->due_date?->format('Y-m-d') ?? '';
        $this->notes = $invoice->notes ?? '';
        $this->discount_amount = (string) $invoice->discount_amount;
        $this->tax_amount = (string) $invoice->tax_amount;
        $this->items = $invoice->items->map(fn ($item): array => [
            'product_id' => $item->product_id ? (string) $item->product_id : '',
            'item_name' => $item->item_name,
            'description' => $item->description ?? '',
            'quantity' => $item->quantity,
            'unit_price' => (float) $item->unit_price,
        ])->toArray();
    }

    protected function rules(): array
    {
        $tenant = tenant();

        return [
            'customer_id' => ['required', 'integer', $tenant->exists('customers', 'id')],
            'order_id' => ['nullable', 'integer', $tenant->exists('orders', 'id')],
            'stock_location_id' => ['nullable', 'integer', $tenant->exists('stock_locations', 'id')],
            'currency_id' => ['required', 'integer', $tenant->exists('currencies', 'id')],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'notes' => ['nullable', 'string'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'integer', $tenant->exists('products', 'id')],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
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
                $this->items[(int) $index]['item_name'] = $product->name;
                if (empty($this->items[(int) $index]['unit_price'])) {
                    $this->items[(int) $index]['unit_price'] = (float) ($product->base_price ?? 0);
                }
            }
        }
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => '',
            'item_name' => '',
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);

        if ($this->items === []) {
            $this->items[] = ['product_id' => '', 'item_name' => '', 'description' => '', 'quantity' => 1, 'unit_price' => 0];
        }
    }

    public function update(UpdateInvoiceAction $action): mixed
    {
        abort_unless(auth()->user()?->can('invoices.update'), 403);

        $invoice = Invoice::query()->findOrFail($this->invoiceId);

        if ($invoice->status !== 'draft') {
            session()->flash('error', 'Only draft invoices can be edited.');

            return $this->redirectRoute('invoices.show', $invoice);
        }

        $this->validate();

        $action->handle($invoice, [
            'customer_id' => (int) $this->customer_id,
            'order_id' => $this->order_id !== '' ? (int) $this->order_id : null,
            'stock_location_id' => $this->stock_location_id !== '' ? (int) $this->stock_location_id : null,
            'currency_id' => (int) $this->currency_id,
            'invoice_date' => $this->invoice_date,
            'due_date' => $this->due_date !== '' ? $this->due_date : null,
            'notes' => $this->notes !== '' ? $this->notes : null,
            'discount_amount' => (float) $this->discount_amount,
            'tax_amount' => (float) $this->tax_amount,
            'items' => collect($this->items)->map(fn ($item): array => [
                'product_id' => isset($item['product_id']) && $item['product_id'] !== '' ? (int) $item['product_id'] : null,
                'item_name' => $item['item_name'],
                'description' => $item['description'] !== '' ? $item['description'] : null,
                'quantity' => (int) $item['quantity'],
                'unit_price' => (float) $item['unit_price'],
            ])->toArray(),
        ]);

        session()->flash('success', 'Invoice updated successfully.');

        return $this->redirectRoute('invoices.show', $invoice);
    }

    public function getSubtotalProperty(): float
    {
        return collect($this->items)->sum(
            fn ($item): float => ((float) ($item['quantity'] ?? 0)) * ((float) ($item['unit_price'] ?? 0))
        );
    }

    public function getTotalProperty(): float
    {
        return $this->subtotal - (float) $this->discount_amount + (float) $this->tax_amount;
    }

    public function render(): View
    {
        $customerId = $this->customer_id !== '' ? (int) $this->customer_id : null;

        $invoice = Invoice::query()->findOrFail($this->invoiceId);

        return view('livewire.invoices.edit-page', [
            'invoice' => $invoice,
            'customers' => Customer::query()->orderBy('full_name')->get(),
            'orders' => $customerId !== null
                ? Order::query()
                    ->where('customer_id', $customerId)
                    ->where(function (Builder $query): void {
                        $query->whereDoesntHave('invoice')
                            ->orWhere('id', $this->order_id !== '' ? (int) $this->order_id : null);
                    })
                    ->orderByDesc('order_date')
                    ->get()
                : collect(),
            'currencies' => Currency::active()->ordered()->get(),
            'products' => Product::query()->active()->orderBy('name')->get(),
            'stockLocations' => StockLocation::query()->active()->ordered()->get(),
        ]);
    }
}
