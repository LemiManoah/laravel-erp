<?php

declare(strict_types=1);

namespace App\Livewire\InventoryItems;

use App\Enums\InventoryItemType;
use App\Livewire\InventoryItems\Concerns\InteractsWithInventoryItemForm;
use App\Models\InventoryItem;
use App\Models\ItemCategory;
use App\Models\UnitOfMeasure;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class CreatePage extends Component
{
    use InteractsWithInventoryItemForm;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('inventory-items.create'), 403);
    }

    public function save(): mixed
    {
        abort_unless(auth()->user()?->can('inventory-items.create'), 403);

        $this->validate($this->inventoryItemRules());

        $inventoryItem = InventoryItem::create($this->inventoryItemPayload());
        $inventoryItem->defaultPrice()->updateOrCreate(
            ['tenant_id' => tenant('id'), 'inventory_item_id' => $inventoryItem->id],
            $this->pricePayload(),
        );

        return redirect()->route('inventory-items.index')
            ->with('success', 'Inventory item created successfully.');
    }

    public function render(): View
    {
        return view('livewire.inventory_items.create-page', [
            'categories' => ItemCategory::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'units' => UnitOfMeasure::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'parentInventoryItems' => InventoryItem::query()
                ->orderBy('name')
                ->get(),
            'itemTypes' => InventoryItemType::cases(),
        ]);
    }
}


