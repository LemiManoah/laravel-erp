<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

final class ProductPrice extends Model
{
    use BelongsToTenant;
    use LogsModelActivity;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'sale_price',
        'purchase_price',
    ];

    protected function casts(): array
    {
        return [
            'sale_price' => 'decimal:2',
            'purchase_price' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
