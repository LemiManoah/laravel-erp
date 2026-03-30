<?php

declare(strict_types=1);

namespace App\Livewire\Orders;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class IndexPage extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('viewAny', Order::class), 403);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->resetPage();
    }

    public function render(): View
    {
        $search = trim($this->search);

        $orders = Order::query()
            ->with(['customer', 'invoice'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($orderQuery) use ($search): void {
                    $orderQuery->where('order_number', 'like', sprintf('%%%s%%', $search))
                        ->orWhereHas('customer', function ($customerQuery) use ($search): void {
                            $customerQuery->where('full_name', 'like', sprintf('%%%s%%', $search))
                                ->orWhere('email', 'like', sprintf('%%%s%%', $search))
                                ->orWhere('phone', 'like', sprintf('%%%s%%', $search));
                        });
                });
            })
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->latest()
            ->paginate(10);

        return view('livewire.orders.index-page', [
            'orders' => $orders,
        ]);
    }
}
