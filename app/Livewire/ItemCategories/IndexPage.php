<?php

declare(strict_types=1);

namespace App\Livewire\ItemCategories;

use App\Models\ItemCategory;
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
        abort_unless(auth()->user()?->can('inventory-items.view'), 403);
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

    public function delete(int $categoryId): void
    {
        abort_unless(auth()->user()?->can('inventory-items.update'), 403);

        $category = ItemCategory::query()->findOrFail($categoryId);

        if ($category->inventoryItems()->exists()) {
            session()->flash('error', 'Cannot delete this item category because it is currently assigned to one or more inventory items. Consider marking it as inactive instead.');

            return;
        }

        $category->delete();

        session()->flash('success', 'Item category deleted successfully.');
    }

    public function render(): View
    {
        $search = trim($this->search);

        $categories = ItemCategory::query()
            ->withCount('inventoryItems')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($catQuery) use ($search): void {
                    $catQuery->where('name', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('description', 'like', sprintf('%%%s%%', $search));
                });
            })
            ->when($this->status !== '', function ($query): void {
                $query->where('is_active', $this->status === 'active');
            })
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.item_categories.index-page', [
            'categories' => $categories,
        ]);
    }
}


