<?php

declare(strict_types=1);

namespace App\Livewire\InventoryItems;

use App\Enums\InventoryItemType;
use App\Livewire\InventoryItems\Concerns\InteractsWithInventoryItemForm;
use App\Models\InventoryItem;
use App\Models\ItemCategory;
use App\Models\UnitOfMeasure;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class EditPage extends Component
{
    use InteractsWithInventoryItemForm;

    #[Locked]
    public int $inventoryItemId;

    public function mount(InventoryItem $inventoryItem): void
    {
        abort_unless(auth()->user()?->can('inventory-items.update'), 403);

        $this->inventoryItemId = $inventoryItem->id;
        $this->fillFromInventoryItem($inventoryItem);
    }

    public function update(): mixed
    {
        abort_unless(auth()->user()?->can('inventory-items.update'), 403);

        $this->validate($this->inventoryItemRules());

        $inventoryItem = InventoryItem::query()->findOrFail($this->inventoryItemId);
        $inventoryItem->update($this->inventoryItemPayload());
        $inventoryItem->defaultPrice()->updateOrCreate(
            ['tenant_id' => tenant('id'), 'inventory_item_id' => $inventoryItem->id],
            $this->pricePayload(),
        );

        return redirect()->route('inventory-items.index')
            ->with('success', 'Inventory item updated successfully.');
    }

    public function render(): View
    {
        return view('livewire.inventory_items.edit-page', [
            'categories' => ItemCategory::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'units' => UnitOfMeasure::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'parentInventoryItems' => InventoryItem::query()
                ->whereKeyNot($this->inventoryItemId)
                ->orderBy('name')
                ->get(),
            'itemTypes' => InventoryItemType::cases(),
        ]);
    }
}


