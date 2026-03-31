<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PurchaseReceiptStatus;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

final class PurchaseReceipt extends Model
{
    use BelongsToTenant;
    use LogsModelActivity;

    protected $fillable = [
        'tenant_id',
        'receipt_number',
        'supplier_id',
        'purchase_order_id',
        'stock_location_id',
        'receipt_date',
        'status',
        'subtotal_amount',
        'notes',
        'created_by',
        'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PurchaseReceiptStatus::class,
            'receipt_date' => 'date',
            'subtotal_amount' => 'decimal:2',
            'posted_at' => 'datetime',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function stockLocation(): BelongsTo
    {
        return $this->belongsTo(StockLocation::class, 'stock_location_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReceiptItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'reference_id')
            ->where('reference_type', 'purchase_receipt');
    }
}
