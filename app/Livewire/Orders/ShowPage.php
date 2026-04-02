<?php

declare(strict_types=1);

namespace App\Livewire\Orders;

use App\Actions\Order\DeleteOrderAction;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class ShowPage extends Component
{
    public Order $order;
    public string $status = '';

    public function mount(Order $order): void
    {
        abort_unless(auth()->user()?->can('orders.view'), 403);

        $this->order = $order->load(['customer', 'items', 'invoice', 'creator', 'assignee', 'currency']);
        $this->status = $order->status;
    }

    public function updateStatus(): void
    {
        abort_unless(auth()->user()?->can('orders.update'), 403);

        $this->validate(['status' => ['required', 'string', 'in:draft,confirmed,in_cutting,in_stitching,in_finishing,awaiting_fitting,ready_for_delivery,delivered,cancelled']]);

        $this->order->update(['status' => $this->status]);
        $this->order->refresh()->load(['customer', 'items', 'invoice', 'creator', 'assignee', 'currency']);
        session()->flash('success', 'Order status updated.');
    }

    public function delete(DeleteOrderAction $action): mixed
    {
        abort_unless(auth()->user()?->can('orders.delete'), 403);

        $action->handle($this->order);

        session()->flash('success', 'Order deleted.');

        return $this->redirectRoute('orders.index');
    }

    public function render(): View
    {
        return view('livewire.orders.show-page');
    }
}
