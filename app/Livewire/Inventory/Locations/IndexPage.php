<?php

declare(strict_types=1);

namespace App\Livewire\Inventory\Locations;

use App\Models\StockLocation;
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

    public bool $confirmingDeletion = false;

    #[Locked]
    public ?int $deletingLocationId = null;

    public string $deletingLocationName = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('stock-locations.view'), 403);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function confirmDelete(int $locationId): void
    {
        abort_unless(auth()->user()?->can('stock-locations.delete'), 403);

        $location = StockLocation::query()->findOrFail($locationId);

        if ($location->inventoryMovements()->exists()) {
            session()->flash('error', 'Cannot delete this location because it has inventory movements. Consider marking it as inactive instead.');

            return;
        }

        $this->deletingLocationId = $location->id;
        $this->deletingLocationName = $location->name;
        $this->confirmingDeletion = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeletion = false;
        $this->deletingLocationId = null;
        $this->deletingLocationName = '';
    }

    public function deleteLocation(): void
    {
        abort_unless(auth()->user()?->can('stock-locations.delete'), 403);

        $location = StockLocation::query()->findOrFail($this->deletingLocationId);
        $location->delete();

        $this->cancelDelete();
        session()->flash('success', 'Stock location deleted successfully.');
    }

    public function render(): View
    {
        $search = trim($this->search);

        $locations = StockLocation::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($locationQuery) use ($search): void {
                    $locationQuery->where('name', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('code', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('location_type', 'like', sprintf('%%%s%%', $search));
                });
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.inventory.locations.index-page', [
            'locations' => $locations,
        ]);
    }
}
