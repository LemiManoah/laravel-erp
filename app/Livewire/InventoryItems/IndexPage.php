<?php

declare(strict_types=1);

namespace App\Livewire\InventoryItems;

use App\Models\InventoryItem;
use App\Models\ItemCategory;
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

    #[Url(except: '')]
    public string $category = '';

    #[Url(except: '')]
    public string $itemType = '';

    public bool $confirmingDeletion = false;

    #[Locked]
    public ?int $deletingInventoryItemId = null;

    public string $deletingInventoryItemName = '';

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

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedItemType(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->category = '';
        $this->itemType = '';
        $this->resetPage();
    }

    public function confirmDelete(int $inventoryItemId): void
    {
        abort_unless(auth()->user()?->can('inventory-items.update'), 403);

        $inventoryItem = InventoryItem::query()->findOrFail($inventoryItemId);

        $this->deletingInventoryItemId = $inventoryItem->id;
        $this->deletingInventoryItemName = $inventoryItem->name;
        $this->confirmingDeletion = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeletion = false;
        $this->deletingInventoryItemId = null;
        $this->deletingInventoryItemName = '';
    }

    public function deleteInventoryItem(): void
    {
        abort_unless(auth()->user()?->can('inventory-items.update'), 403);

        $inventoryItem = InventoryItem::query()->findOrFail($this->deletingInventoryItemId);
        $inventoryItem->delete();

        $this->cancelDelete();
        session()->flash('success', 'Inventory item deleted successfully.');
    }

    public function render(): View
    {
        $categories = ItemCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $inventoryItems = InventoryItem::query()
            ->with(['baseUnit', 'category', 'defaultPrice', 'inventoryStocks'])
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($inventoryItemQuery): void {
                    $search = trim($this->search);

                    $inventoryItemQuery->where('name', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('description', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('sku', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('barcode', 'like', sprintf('%%%s%%', $search));
                });
            })
            ->when($this->status !== '', function ($query): void {
                $query->where('is_active', $this->status === 'active');
            })
            ->when($this->category !== '', function ($query): void {
                $query->where('item_category_id', $this->category);
            })
            ->when($this->itemType !== '', function ($query): void {
                $query->where('item_type', $this->itemType);
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.inventory_items.index-page', [
            'categories' => $categories,
            'itemTypes' => \App\Enums\InventoryItemType::cases(),
            'inventoryItems' => $inventoryItems,
        ]);
    }
}

