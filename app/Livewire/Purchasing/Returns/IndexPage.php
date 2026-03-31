<?php

declare(strict_types=1);

namespace App\Livewire\Purchasing\Returns;

use App\Models\PurchaseReturn;
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
        abort_unless(auth()->user()?->can('purchase-returns.view'), 403);
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

        $returns = PurchaseReturn::query()
            ->with(['supplier', 'purchaseReceipt', 'stockLocation', 'creator'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($returnQuery) use ($search): void {
                    $returnQuery->where('return_number', 'like', sprintf('%%%s%%', $search))
                        ->orWhereHas('supplier', fn ($supplierQuery) => $supplierQuery->where('name', 'like', sprintf('%%%s%%', $search)))
                        ->orWhereHas('purchaseReceipt', fn ($receiptQuery) => $receiptQuery->where('receipt_number', 'like', sprintf('%%%s%%', $search)));
                });
            })
            ->when($this->supplier !== '', fn ($query) => $query->where('supplier_id', (int) $this->supplier))
            ->latest('return_date')
            ->latest('id')
            ->paginate(10);

        return view('livewire.purchasing.returns.index-page', [
            'returns' => $returns,
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
