<?php

declare(strict_types=1);

namespace App\Livewire\Inventory\Units;

use App\Models\UnitOfMeasure;
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
    public ?int $deletingUnitId = null;

    public string $deletingUnitName = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('units-of-measure.view'), 403);
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

    public function confirmDelete(int $unitId): void
    {
        abort_unless(auth()->user()?->can('units-of-measure.delete'), 403);

        $unit = UnitOfMeasure::query()->findOrFail($unitId);

        if ($unit->inventoryItems()->exists()) {
            session()->flash('error', 'Cannot delete this unit because it is currently assigned to one or more inventory items. Consider marking it as inactive instead.');

            return;
        }

        $this->deletingUnitId = $unit->id;
        $this->deletingUnitName = $unit->name;
        $this->confirmingDeletion = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeletion = false;
        $this->deletingUnitId = null;
        $this->deletingUnitName = '';
    }

    public function deleteUnit(): void
    {
        abort_unless(auth()->user()?->can('units-of-measure.delete'), 403);

        $unit = UnitOfMeasure::query()->findOrFail($this->deletingUnitId);
        $unit->delete();

        $this->cancelDelete();
        session()->flash('success', 'Unit of measure deleted successfully.');
    }

    public function render(): View
    {
        $search = trim($this->search);

        $units = UnitOfMeasure::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($unitQuery) use ($search): void {
                    $unitQuery->where('name', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('abbreviation', 'like', sprintf('%%%s%%', $search));
                });
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.inventory.units.index-page', [
            'units' => $units,
        ]);
    }
}


