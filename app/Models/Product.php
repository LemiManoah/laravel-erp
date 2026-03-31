<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductItemType;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * @property int $id
 * @property string $tenant_id
 * @property int|null $product_category_id
 * @property string|null $sku
 * @property string|null $barcode
 * @property ProductItemType $item_type
 * @property bool $tracks_inventory
 * @property bool $is_sellable
 * @property bool $is_purchasable
 * @property int|null $base_unit_id
 * @property float|null $reorder_level
 * @property float|null $reorder_quantity
 * @property bool $has_variants
 * @property int|null $parent_item_id
 * @property bool $allow_negative_stock
 * @property bool $has_expiry
 * @property bool $is_serialized
 * @property string $name
 * @property string|null $description
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class Product extends Model
{
    use BelongsToTenant;
    use LogsModelActivity;

    protected $fillable = [
        'product_category_id',
        'sku',
        'barcode',
        'item_type',
        'tracks_inventory',
        'is_sellable',
        'is_purchasable',
        'base_unit_id',
        'reorder_level',
        'reorder_quantity',
        'has_variants',
        'parent_item_id',
        'allow_negative_stock',
        'has_expiry',
        'is_serialized',
        'name',
        'description',
        'is_active',
    ];

    protected $appends = [
        'base_price',
        'buying_price',
        'quantity_on_hand',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'item_type' => ProductItemType::class,
            'tracks_inventory' => 'boolean',
            'is_sellable' => 'boolean',
            'is_purchasable' => 'boolean',
            'has_variants' => 'boolean',
            'allow_negative_stock' => 'boolean',
            'has_expiry' => 'boolean',
            'is_serialized' => 'boolean',
            'reorder_level' => 'decimal:2',
            'reorder_quantity' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'base_unit_id');
    }

    public function parentItem(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'parent_item_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Product::class, 'parent_item_id');
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'product_id');
    }

    public function inventoryStocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class, 'product_id');
    }

    public function defaultPrice(): HasOne
    {
        return $this->hasOne(ProductPrice::class, 'product_id');
    }

    public function purchaseReceiptItems(): HasMany
    {
        return $this->hasMany(PurchaseReceiptItem::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function purchaseReturnItems(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    protected function isLowStock(): Attribute
    {
        return Attribute::get(function () {
            if ($this->reorder_level === null) {
                return false;
            }

            return (float) $this->quantity_on_hand <= (float) $this->reorder_level;
        });
    }

    protected function isOutOfStock(): Attribute
    {
        return Attribute::get(fn () => (float) $this->quantity_on_hand <= 0);
    }

    protected function quantityOnHand(): Attribute
    {
        return Attribute::get(function (): string {
            $total = $this->relationLoaded('inventoryStocks')
                ? $this->inventoryStocks->sum(fn (InventoryStock $stock): float => (float) $stock->quantity_on_hand)
                : (float) $this->inventoryStocks()->sum('quantity_on_hand');

            return number_format($total, 2, '.', '');
        });
    }

    protected function basePrice(): Attribute
    {
        return Attribute::get(function (): ?string {
            $price = $this->relationLoaded('defaultPrice')
                ? $this->defaultPrice?->selling_price
                : $this->defaultPrice()->value('selling_price');

            return $price === null ? null : number_format((float) $price, 2, '.', '');
        });
    }

    protected function buyingPrice(): Attribute
    {
        return Attribute::get(function (): ?string {
            $price = $this->relationLoaded('defaultPrice')
                ? $this->defaultPrice?->buying_price
                : $this->defaultPrice()->value('buying_price');

            return $price === null ? null : number_format((float) $price, 2, '.', '');
        });
    }

    public function scopeStockTracked($query)
    {
        return $query->where('tracks_inventory', true);
    }

    public function scopeSellable($query)
    {
        return $query->where('is_sellable', true);
    }

    public function scopePurchasable($query)
    {
        return $query->where('is_purchasable', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereNotNull('reorder_level');
    }

    public function scopeOutOfStock($query)
    {
        return $query;
    }

    public function scopeBySku($query, string $sku)
    {
        return $query->where('sku', $sku);
    }

    public function scopeByBarcode($query, string $barcode)
    {
        return $query->where('barcode', $barcode);
    }
}
