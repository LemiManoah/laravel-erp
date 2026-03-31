<?php

declare(strict_types=1);

namespace App\Livewire\Purchasing\Receipts;

use App\Models\PurchaseReceipt;
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

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('purchase-receipts.view'), 403);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSupplier(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->supplier = '';
        $this->resetPage();
    }

    public function render(): View
    {
        $search = trim($this->search);

        $receipts = PurchaseReceipt::query()
            ->with(['supplier', 'stockLocation', 'creator'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($receiptQuery) use ($search): void {
                    $receiptQuery->where('receipt_number', 'like', sprintf('%%%s%%', $search))
                        ->orWhereHas('supplier', fn ($supplierQuery) => $supplierQuery->where('name', 'like', sprintf('%%%s%%', $search)))
                        ->orWhereHas('stockLocation', fn ($locationQuery) => $locationQuery->where('name', 'like', sprintf('%%%s%%', $search)));
                });
            })
            ->when($this->supplier !== '', fn ($query) => $query->where('supplier_id', (int) $this->supplier))
            ->latest('receipt_date')
            ->latest('id')
            ->paginate(10);

        return view('livewire.purchasing.receipts.index-page', [
            'receipts' => $receipts,
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
