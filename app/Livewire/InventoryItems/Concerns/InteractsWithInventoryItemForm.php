<?php

declare(strict_types=1);

namespace App\Livewire\InventoryItems\Concerns;

use App\Enums\InventoryItemType;
use App\Models\InventoryItem;
use Closure;
use Illuminate\Validation\Rule;

trait InteractsWithInventoryItemForm
{
    public ?string $item_category_id = null;

    public ?string $sku = null;

    public ?string $barcode = null;

    public string $item_type = 'stock_item';

    public bool $tracks_inventory = true;

    public bool $is_sellable = true;

    public bool $is_purchasable = true;

    public ?string $base_unit_id = null;

    public ?string $reorder_level = null;

    public bool $has_variants = false;

    public ?string $parent_item_id = null;

    public bool $has_expiry = false;

    public bool $is_serialized = false;

    public string $name = '';

    public ?string $description = null;

    public ?string $purchase_price = null;

    public ?string $sale_price = null;

    public bool $is_active = true;

    /**
     * @return array<string, mixed>
     */
    protected function inventoryItemRules(): array
    {
        $tenant = tenant();

        return [
            'item_category_id' => ['nullable', $tenant->exists('item_categories', 'id')],
            'item_type' => ['required', Rule::enum(InventoryItemType::class)],
            'tracks_inventory' => ['boolean'],
            'is_sellable' => ['boolean'],
            'is_purchasable' => ['boolean'],
            'base_unit_id' => ['nullable', Rule::requiredIf($this->tracks_inventory), $tenant->exists('units_of_measure', 'id')],
            'reorder_level' => ['nullable', 'numeric', 'min:0'],
            'has_variants' => ['boolean'],
            'parent_item_id' => [
                'nullable',
                $tenant->exists('inventory_items', 'id'),
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($value !== null && $value !== '' && (int) $value === $this->currentInventoryItemId()) {
                        $fail('An inventory item cannot be its own parent item.');
                    }
                },
            ],
            'has_expiry' => ['boolean'],
            'is_serialized' => ['boolean'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', Rule::requiredIf($this->is_sellable), 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    protected function fillFromInventoryItem(InventoryItem $inventoryItem): void
    {
        $this->item_category_id = $inventoryItem->item_category_id === null ? null : (string) $inventoryItem->item_category_id;
        $this->sku = $inventoryItem->sku;
        $this->barcode = $inventoryItem->barcode;
        $this->item_type = $inventoryItem->item_type->value;
        $this->tracks_inventory = $inventoryItem->tracks_inventory;
        $this->is_sellable = $inventoryItem->is_sellable;
        $this->is_purchasable = $inventoryItem->is_purchasable;
        $this->base_unit_id = $inventoryItem->base_unit_id === null ? null : (string) $inventoryItem->base_unit_id;
        $this->reorder_level = $inventoryItem->reorder_level === null ? null : (string) $inventoryItem->reorder_level;
        $this->has_variants = $inventoryItem->has_variants;
        $this->parent_item_id = $inventoryItem->parent_item_id === null ? null : (string) $inventoryItem->parent_item_id;
        $this->has_expiry = $inventoryItem->has_expiry;
        $this->is_serialized = $inventoryItem->is_serialized;
        $this->name = $inventoryItem->name;
        $this->description = $inventoryItem->description;
        $this->purchase_price = $inventoryItem->purchase_price === null ? null : (string) $inventoryItem->purchase_price;
        $this->sale_price = $inventoryItem->sale_price === null ? null : (string) $inventoryItem->sale_price;
        $this->is_active = $inventoryItem->is_active;
    }

    public function updatedItemType(string $value): void
    {
        $itemType = InventoryItemType::tryFrom($value);

        if ($itemType !== null && ! $itemType->tracksInventoryByDefault()) {
            $this->tracks_inventory = false;
        }
    }

    public function updatedTracksInventory(bool $value): void
    {
        if ($value) {
            return;
        }

        $this->base_unit_id = null;
        $this->reorder_level = null;
        $this->has_expiry = false;
        $this->is_serialized = false;
    }

    public function updatedIsSellable(bool $value): void
    {
        if ($value) {
            return;
        }

        $this->sale_price = null;
        $this->barcode = null;
    }

    public function updatedHasExpiry(bool $value): void
    {
        if ($value) {
            $this->tracks_inventory = true;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function inventoryItemPayload(): array
    {
        $description = $this->description === null ? null : trim($this->description);
        $tracksInventory = $this->tracks_inventory;

        return [
            'item_category_id' => filled($this->item_category_id) ? (int) $this->item_category_id : null,
            'item_type' => $this->item_type,
            'tracks_inventory' => $tracksInventory,
            'is_sellable' => $this->is_sellable,
            'is_purchasable' => $this->is_purchasable,
            'base_unit_id' => $tracksInventory && filled($this->base_unit_id) ? (int) $this->base_unit_id : null,
            'reorder_level' => $tracksInventory ? $this->trimOrNull($this->reorder_level) : null,
            'has_variants' => $this->has_variants,
            'parent_item_id' => filled($this->parent_item_id) ? (int) $this->parent_item_id : null,
            'has_expiry' => $tracksInventory && $this->has_expiry,
            'is_serialized' => $tracksInventory ? $this->is_serialized : false,
            'name' => trim($this->name),
            'description' => $description === '' ? null : $description,
            'is_active' => $this->is_active,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function pricePayload(): array
    {
        $purchasePrice = $this->purchase_price === null ? null : trim($this->purchase_price);
        $salePrice = $this->sale_price === null ? null : trim($this->sale_price);

        return [
            'purchase_price' => $purchasePrice === '' ? null : $purchasePrice,
            'sale_price' => $salePrice === '' ? null : $salePrice,
        ];
    }

    protected function currentInventoryItem(): ?InventoryItem
    {
        $inventoryItemId = $this->currentInventoryItemId();

        if ($inventoryItemId === null) {
            return null;
        }

        return InventoryItem::query()->find($inventoryItemId);
    }

    protected function currentInventoryItemId(): ?int
    {
        if (! property_exists($this, 'inventoryItemId')) {
            return null;
        }

        $inventoryItemId = $this->inventoryItemId;

        return is_int($inventoryItemId) ? $inventoryItemId : null;
    }

    protected function trimOrNull(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}


