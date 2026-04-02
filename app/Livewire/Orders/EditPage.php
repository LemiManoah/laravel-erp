<?php

declare(strict_types=1);

namespace App\Livewire\Orders;

use App\Actions\Order\UpdateOrderAction;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class EditPage extends Component
{
    #[Locked]
    public int $orderId;

    public string $customer_id = '';
    public string $currency_id = '';
    public string $order_date = '';
    public string $promised_delivery_date = '';
    public string $status = '';
    public string $priority = 'medium';
    public string $notes = '';

    public function mount(Order $order): void
    {
        abort_unless(auth()->user()?->can('orders.update'), 403);

        $this->orderId = $order->id;
        $this->customer_id = (string) $order->customer_id;
        $this->currency_id = (string) $order->currency_id;
        $this->order_date = $order->order_date->format('Y-m-d');
        $this->promised_delivery_date = $order->promised_delivery_date?->format('Y-m-d') ?? '';
        $this->status = $order->status;
        $this->priority = $order->priority;
        $this->notes = $order->notes ?? '';
    }

    protected function rules(): array
    {
        $tenant = tenant();

        return [
            'customer_id' => ['required', 'integer', $tenant->exists('customers', 'id')],
            'currency_id' => ['required', 'integer', $tenant->exists('currencies', 'id')],
            'order_date' => ['required', 'date'],
            'promised_delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'status' => ['required', 'string', 'in:draft,confirmed,in_cutting,in_stitching,in_finishing,awaiting_fitting,ready_for_delivery,delivered,cancelled'],
            'priority' => ['required', 'string', 'in:low,medium,high,urgent'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function update(UpdateOrderAction $action): mixed
    {
        abort_unless(auth()->user()?->can('orders.update'), 403);

        $this->validate();

        $order = Order::query()->findOrFail($this->orderId);

        $action->handle($order, [
            'customer_id' => (int) $this->customer_id,
            'currency_id' => (int) $this->currency_id,
            'order_date' => $this->order_date,
            'promised_delivery_date' => $this->promised_delivery_date !== '' ? $this->promised_delivery_date : null,
            'status' => $this->status,
            'priority' => $this->priority,
            'notes' => $this->notes !== '' ? $this->notes : null,
        ]);

        session()->flash('success', 'Order updated successfully.');

        return $this->redirectRoute('orders.show', $order);
    }

    public function render(): View
    {
        return view('livewire.orders.edit-page', [
            'customers' => Customer::query()->orderBy('full_name')->get(),
            'currencies' => Currency::active()->ordered()->get(),
        ]);
    }
}
