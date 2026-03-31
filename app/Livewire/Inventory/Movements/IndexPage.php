<?php

declare(strict_types=1);

namespace App\Livewire\Inventory\Movements;

use App\Enums\InventoryDirection;
use App\Enums\InventoryMovementType;
use App\Models\InventoryMovement;
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
    public string $movementType = '';

    #[Url(except: '')]
    public string $direction = '';

    #[Url(except: '')]
    public string $location = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('inventory-movements.view'), 403);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedMovementType(): void
    {
        $this->resetPage();
    }

    public function updatedDirection(): void
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
        $this->movementType = '';
        $this->direction = '';
        $this->location = '';
        $this->resetPage();
    }

    public function render(): View
    {
        $search = trim($this->search);

        $movements = InventoryMovement::query()
            ->with(['inventoryStock', 'location', 'product'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($movementQuery) use ($search): void {
                    $movementQuery->whereHas('product', fn ($productQuery) => $productQuery->where('name', 'like', sprintf('%%%s%%', $search)))
                        ->orWhereHas('location', fn ($locationQuery) => $locationQuery->where('name', 'like', sprintf('%%%s%%', $search)))
                        ->orWhereHas('inventoryStock', fn ($stockQuery) => $stockQuery->where('batch_number', 'like', sprintf('%%%s%%', $search)))
                        ->orWhere('reference_type', 'like', sprintf('%%%s%%', $search));
                });
            })
            ->when($this->movementType !== '', fn ($query) => $query->where('movement_type', $this->movementType))
            ->when($this->direction !== '', fn ($query) => $query->where('direction', $this->direction))
            ->when($this->location !== '', fn ($query) => $query->where('location_id', $this->location))
            ->latest('movement_date')
            ->paginate(15);

        return view('livewire.inventory.movements.index-page', [
            'movements' => $movements,
            'movementTypes' => InventoryMovementType::cases(),
            'directions' => InventoryDirection::cases(),
            'locations' => StockLocation::query()->active()->ordered()->get(),
        ]);
    }
}
