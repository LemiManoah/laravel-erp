<?php

declare(strict_types=1);

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
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

    public bool $confirmingDeletion = false;

    #[Locked]
    public ?int $deletingSupplierId = null;

    public string $deletingSupplierName = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('suppliers.view'), 403);
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

    public function confirmDelete(int $supplierId): void
    {
        abort_unless(auth()->user()?->can('suppliers.delete'), 403);

        $supplier = Supplier::query()->findOrFail($supplierId);

        if ($supplier->purchaseReceipts()->exists()) {
            session()->flash('error', 'Cannot delete this supplier because purchase receipts already exist for it.');

            return;
        }

        $this->deletingSupplierId = $supplier->id;
        $this->deletingSupplierName = $supplier->name;
        $this->confirmingDeletion = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeletion = false;
        $this->deletingSupplierId = null;
        $this->deletingSupplierName = '';
    }

    public function deleteSupplier(): void
    {
        abort_unless(auth()->user()?->can('suppliers.delete'), 403);

        $supplier = Supplier::query()->findOrFail($this->deletingSupplierId);
        $supplier->delete();

        $this->cancelDelete();
        session()->flash('success', 'Supplier deleted successfully.');
    }

    public function render(): View
    {
        $search = trim($this->search);

        $suppliers = Supplier::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($supplierQuery) use ($search): void {
                    $supplierQuery->where('name', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('code', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('contact_person', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('phone', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('email', 'like', sprintf('%%%s%%', $search));
                });
            })
            ->when($this->status !== '', fn ($query) => $query->where('is_active', $this->status === 'active'))
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.suppliers.index-page', [
            'suppliers' => $suppliers,
        ]);
    }
}
