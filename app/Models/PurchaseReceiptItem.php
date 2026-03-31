<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

final class PurchaseReceiptItem extends Model
{
    use BelongsToTenant;
    use LogsModelActivity;

    protected $fillable = [
        'tenant_id',
        'purchase_receipt_id',
        'product_id',
        'quantity',
        'unit_cost',
        'line_total',
        'batch_number',
        'expiry_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'line_total' => 'decimal:2',
            'expiry_date' => 'date',
        ];
    }

    public function purchaseReceipt(): BelongsTo
    {
        return $this->belongsTo(PurchaseReceipt::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
