<?php

declare(strict_types=1);

namespace App\Livewire\Inventory\Batches;

use App\Enums\InventoryBatchStatus;
use App\Models\InventoryBatch;
use App\Models\StockLocation;
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

    #[Url(except: '')]
    public string $expiry = '';

    #[Url(except: '')]
    public string $location = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('inventory-batches.view'), 403);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedExpiry(): void
    {
        $this->resetPage();
    }

    public function updatedLocation(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->expiry = '';
        $this->location = '';
        $this->resetPage();
    }

    public function render(): View
    {
        $search = trim($this->search);

        $batches = InventoryBatch::query()
            ->with(['location', 'product'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($batchQuery) use ($search): void {
                    $batchQuery->where('batch_number', 'like', sprintf('%%%s%%', $search))
                        ->orWhereHas('product', fn ($productQuery) => $productQuery->where('name', 'like', sprintf('%%%s%%', $search)))
                        ->orWhereHas('location', fn ($locationQuery) => $locationQuery->where('name', 'like', sprintf('%%%s%%', $search)));
                });
            })
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->when($this->location !== '', fn ($query) => $query->where('location_id', $this->location))
            ->when($this->expiry === 'near_expiry', fn ($query) => $query->nearExpiry())
            ->when($this->expiry === 'expired', fn ($query) => $query->expired())
            ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expiry_date')
            ->orderBy('batch_number')
            ->paginate(12);

        return view('livewire.inventory.batches.index-page', [
            'batches' => $batches,
            'statuses' => InventoryBatchStatus::cases(),
            'locations' => StockLocation::query()->active()->ordered()->get(),
        ]);
    }
}
