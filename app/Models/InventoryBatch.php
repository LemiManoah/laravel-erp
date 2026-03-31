<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InventoryBatchStatus;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * @property int $id
 * @property string $tenant_id
 * @property int $product_id
 * @property int|null $location_id
 * @property string $batch_number
 * @property Carbon|null $expiry_date
 * @property Carbon|null $manufactured_at
 * @property Carbon|null $received_at
 * @property float $quantity_on_hand
 * @property float|null $cost_price
 * @property InventoryBatchStatus $status
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class InventoryBatch extends Model
{
    use BelongsToTenant;
    use LogsModelActivity;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'location_id',
        'batch_number',
        'expiry_date',
        'manufactured_at',
        'received_at',
        'quantity_on_hand',
        'cost_price',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'manufactured_at' => 'date',
            'received_at' => 'date',
            'quantity_on_hand' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'status' => InventoryBatchStatus::class,
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

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'batch_id');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query
            ->where('quantity_on_hand', '>', 0)
            ->where('status', InventoryBatchStatus::Active);
    }

    public function scopeNearExpiry(Builder $query, int $days = 30): Builder
    {
        return $query
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', now()->toDateString())
            ->whereDate('expiry_date', '<=', now()->addDays($days)->toDateString());
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', now()->toDateString());
    }

    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->lt(today());
    }
}
