<?php

declare(strict_types=1);

namespace App\Livewire\Products\Concerns;

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

    public ?string $reorder_quantity = null;

    public ?string $opening_stock_quantity = null;

    public ?string $opening_stock_date = null;

    public bool $has_variants = false;

    public ?string $parent_item_id = null;

    public bool $allow_negative_stock = false;

    public bool $has_expiry = false;

    public bool $requires_batch_tracking = false;

    public bool $is_serialized = false;

    public string $name = '';

    public ?string $description = null;

    public ?string $base_price = null;

    public bool $is_active = true;

    /**
     * @return array<string, mixed>
     */
    protected function productRules(): array
    {
        $tenant = tenant();
        $product = $this->currentProduct();
        $skuRule = $tenant->unique('products', 'sku');
        $barcodeRule = $tenant->unique('products', 'barcode');

        if ($product !== null) {
            $skuRule->ignore($product);
            $barcodeRule->ignore($product);
        }

        return [
            'product_category_id' => ['nullable', $tenant->exists('product_categories', 'id')],
            'sku' => ['nullable', 'string', 'max:255', $skuRule],
            'barcode' => ['nullable', 'string', 'max:255', $barcodeRule],
            'item_type' => ['required', Rule::in([
                'service',
                'stock_item',
                'non_stock_item',
                'raw_material',
                'finished_good',
                'consumable',
            ])],
            'tracks_inventory' => ['boolean'],
            'is_sellable' => ['boolean'],
            'is_purchasable' => ['boolean'],
            'base_unit_id' => ['nullable', Rule::requiredIf($this->tracks_inventory), $tenant->exists('units_of_measure', 'id')],
            'reorder_level' => ['nullable', 'numeric', 'min:0'],
            'reorder_quantity' => ['nullable', 'numeric', 'min:0'],
            'opening_stock_quantity' => ['nullable', 'numeric', 'min:0'],
            'opening_stock_date' => ['nullable', 'date'],
            'has_variants' => ['boolean'],
            'parent_item_id' => [
                'nullable',
                $tenant->exists('products', 'id'),
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($value !== null && $value !== '' && (int) $value === $this->currentProductId()) {
                        $fail('A product cannot be its own parent item.');
                    }
                },
            ],
            'allow_negative_stock' => ['boolean'],
            'has_expiry' => ['boolean'],
            'requires_batch_tracking' => [
                'boolean',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($this->has_expiry && ! $value) {
                        $fail('Batch tracking is required when the item has expiry.');
                    }
                },
            ],
            'is_serialized' => ['boolean'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    protected function fillFromProduct(Product $product): void
    {
        $this->product_category_id = $product->product_category_id === null ? null : (string) $product->product_category_id;
        $this->sku = $product->sku;
        $this->barcode = $product->barcode;
        $this->item_type = $product->item_type;
        $this->tracks_inventory = $product->tracks_inventory;
        $this->is_sellable = $product->is_sellable;
        $this->is_purchasable = $product->is_purchasable;
        $this->base_unit_id = $product->base_unit_id === null ? null : (string) $product->base_unit_id;
        $this->reorder_level = $product->reorder_level === null ? null : (string) $product->reorder_level;
        $this->reorder_quantity = $product->reorder_quantity === null ? null : (string) $product->reorder_quantity;
        $this->opening_stock_quantity = $product->opening_stock_quantity === null ? null : (string) $product->opening_stock_quantity;
        $this->opening_stock_date = $product->opening_stock_date?->format('Y-m-d\TH:i');
        $this->has_variants = $product->has_variants;
        $this->parent_item_id = $product->parent_item_id === null ? null : (string) $product->parent_item_id;
        $this->allow_negative_stock = $product->allow_negative_stock;
        $this->has_expiry = $product->has_expiry;
        $this->requires_batch_tracking = $product->requires_batch_tracking;
        $this->is_serialized = $product->is_serialized;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->base_price = $product->base_price === null ? null : (string) $product->base_price;
        $this->is_active = $product->is_active;
    }

    public function updatedItemType(string $value): void
    {
        if (in_array($value, ['service', 'non_stock_item'], true)) {
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
        $this->reorder_quantity = null;
        $this->opening_stock_quantity = null;
        $this->opening_stock_date = null;
        $this->allow_negative_stock = false;
        $this->has_expiry = false;
        $this->requires_batch_tracking = false;
        $this->is_serialized = false;
    }

    public function updatedHasExpiry(bool $value): void
    {
        if (! $value) {
            return;
        }

        $this->tracks_inventory = true;
        $this->requires_batch_tracking = true;
    }

    /**
     * @return array<string, mixed>
     */
    protected function productPayload(): array
    {
        $sku = $this->trimOrNull($this->sku);
        $barcode = $this->trimOrNull($this->barcode);
        $description = $this->description === null ? null : trim($this->description);
        $basePrice = $this->base_price === null ? null : trim($this->base_price);
        $tracksInventory = $this->tracks_inventory;
        $hasExpiry = $tracksInventory && $this->has_expiry;
        $requiresBatchTracking = $hasExpiry ? true : ($tracksInventory ? $this->requires_batch_tracking : false);
        $openingStockQuantity = $tracksInventory ? $this->trimOrNull($this->opening_stock_quantity) : null;

        return [
            'product_category_id' => filled($this->product_category_id) ? (int) $this->product_category_id : null,
            'sku' => $sku,
            'barcode' => $barcode,
            'item_type' => $this->item_type,
            'tracks_inventory' => $tracksInventory,
            'is_sellable' => $this->is_sellable,
            'is_purchasable' => $this->is_purchasable,
            'base_unit_id' => $tracksInventory && filled($this->base_unit_id) ? (int) $this->base_unit_id : null,
            'reorder_level' => $tracksInventory ? $this->trimOrNull($this->reorder_level) : null,
            'reorder_quantity' => $tracksInventory ? $this->trimOrNull($this->reorder_quantity) : null,
            'opening_stock_quantity' => $openingStockQuantity,
            'opening_stock_date' => $tracksInventory ? $this->trimOrNull($this->opening_stock_date) : null,
            'has_variants' => $this->has_variants,
            'parent_item_id' => filled($this->parent_item_id) ? (int) $this->parent_item_id : null,
            'allow_negative_stock' => $tracksInventory ? $this->allow_negative_stock : false,
            'has_expiry' => $hasExpiry,
            'requires_batch_tracking' => $requiresBatchTracking,
            'is_serialized' => $tracksInventory ? $this->is_serialized : false,
            'quantity_on_hand' => $tracksInventory ? ($openingStockQuantity ?? '0') : '0',
            'name' => trim($this->name),
            'description' => $description === '' ? null : $description,
            'base_price' => $basePrice === '' ? null : $basePrice,
            'is_active' => $this->is_active,
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
