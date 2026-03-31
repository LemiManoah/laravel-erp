<?php

declare(strict_types=1);

namespace App\Livewire\Purchasing\Orders;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
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
    public string $supplier = '';

    #[Url(except: '')]
    public string $status = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('purchase-orders.view'), 403);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSupplier(): void
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
        $this->supplier = '';
        $this->status = '';
        $this->resetPage();
    }

    public function render(): View
    {
        $search = trim($this->search);

        $orders = PurchaseOrder::query()
            ->with(['supplier', 'stockLocation', 'creator'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($orderQuery) use ($search): void {
                    $orderQuery->where('order_number', 'like', sprintf('%%%s%%', $search))
                        ->orWhereHas('supplier', fn ($supplierQuery) => $supplierQuery->where('name', 'like', sprintf('%%%s%%', $search)))
                        ->orWhereHas('stockLocation', fn ($locationQuery) => $locationQuery->where('name', 'like', sprintf('%%%s%%', $search)));
                });
            })
            ->when($this->supplier !== '', fn ($query) => $query->where('supplier_id', (int) $this->supplier))
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->latest('order_date')
            ->latest('id')
            ->paginate(10);

        return view('livewire.purchasing.orders.index-page', [
            'orders' => $orders,
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
