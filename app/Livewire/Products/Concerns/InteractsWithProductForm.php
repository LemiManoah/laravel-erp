<?php

declare(strict_types=1);

namespace App\Livewire\Products\Concerns;

use App\Enums\ProductItemType;
use App\Models\Product;
use Closure;
use Illuminate\Validation\Rule;

trait InteractsWithProductForm
{
    public ?string $product_category_id = null;

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
    protected function productRules(): array
    {
        $tenant = tenant();

        return [
            'product_category_id' => ['nullable', $tenant->exists('product_categories', 'id')],
            'item_type' => ['required', Rule::enum(ProductItemType::class)],
            'tracks_inventory' => ['boolean'],
            'is_sellable' => ['boolean'],
            'is_purchasable' => ['boolean'],
            'base_unit_id' => ['nullable', Rule::requiredIf($this->tracks_inventory), $tenant->exists('units_of_measure', 'id')],
            'reorder_level' => ['nullable', 'numeric', 'min:0'],
            'has_variants' => ['boolean'],
            'parent_item_id' => [
                'nullable',
                $tenant->exists('products', 'id'),
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($value !== null && $value !== '' && (int) $value === $this->currentProductId()) {
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

    protected function fillFromProduct(Product $product): void
    {
        $this->product_category_id = $product->product_category_id === null ? null : (string) $product->product_category_id;
        $this->sku = $product->sku;
        $this->barcode = $product->barcode;
        $this->item_type = $product->item_type->value;
        $this->tracks_inventory = $product->tracks_inventory;
        $this->is_sellable = $product->is_sellable;
        $this->is_purchasable = $product->is_purchasable;
        $this->base_unit_id = $product->base_unit_id === null ? null : (string) $product->base_unit_id;
        $this->reorder_level = $product->reorder_level === null ? null : (string) $product->reorder_level;
        $this->has_variants = $product->has_variants;
        $this->parent_item_id = $product->parent_item_id === null ? null : (string) $product->parent_item_id;
        $this->has_expiry = $product->has_expiry;
        $this->is_serialized = $product->is_serialized;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->purchase_price = $product->purchase_price === null ? null : (string) $product->purchase_price;
        $this->sale_price = $product->sale_price === null ? null : (string) $product->sale_price;
        $this->is_active = $product->is_active;
    }

    public function updatedItemType(string $value): void
    {
        $itemType = ProductItemType::tryFrom($value);

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
    protected function productPayload(): array
    {
        $description = $this->description === null ? null : trim($this->description);
        $tracksInventory = $this->tracks_inventory;

        return [
            'product_category_id' => filled($this->product_category_id) ? (int) $this->product_category_id : null,
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

    protected function currentProduct(): ?Product
    {
        $productId = $this->currentProductId();

        if ($productId === null) {
            return null;
        }

        return Product::query()->find($productId);
    }

    protected function currentProductId(): ?int
    {
        if (! property_exists($this, 'productId')) {
            return null;
        }

        $productId = $this->productId;

        return is_int($productId) ? $productId : null;
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
