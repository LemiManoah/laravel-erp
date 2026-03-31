<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InventoryDirection;
use App\Enums\InventoryMovementType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * @property int $id
 * @property string $tenant_id
 * @property int $product_id
 * @property int|null $location_id
 * @property int|null $batch_id
 * @property InventoryMovementType $movement_type
 * @property InventoryDirection $direction
 * @property float $quantity
 * @property int|null $unit_id
 * @property float $unit_conversion_rate
 * @property float|null $balance_after
 * @property float|null $unit_cost
 * @property string|null $reference_type
 * @property int|null $reference_id
 * @property Carbon $movement_date
 * @property string|null $notes
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class InventoryMovement extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'location_id',
        'batch_id',
        'movement_type',
        'direction',
        'quantity',
        'unit_id',
        'unit_conversion_rate',
        'balance_after',
        'unit_cost',
        'reference_type',
        'reference_id',
        'movement_date',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'movement_type' => InventoryMovementType::class,
            'direction' => InventoryDirection::class,
            'quantity' => 'decimal:2',
            'unit_conversion_rate' => 'decimal:4',
            'balance_after' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'movement_date' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(StockLocation::class, 'location_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class, 'batch_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'unit_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    public function scopeForLocation(Builder $query, int $locationId): Builder
    {
        return $query->where('location_id', $locationId);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('movement_type', $type);
    }

    public function scopeDirectionIn(Builder $query): Builder
    {
        return $query->where('direction', 'in');
    }

    public function scopeDirectionOut(Builder $query): Builder
    {
        return $query->where('direction', 'out');
    }

    public function scopeWithinDateRange(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('movement_date', [$from, $to]);
    }
}
