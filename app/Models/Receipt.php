<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Receipt extends Model
{
    use BelongsToTenant;
    use LogsModelActivity;

    protected $fillable = [
        'receipt_number',
        'payment_id',
        'issued_date',
    ];

    protected $casts = [
        'issued_date' => 'date',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
