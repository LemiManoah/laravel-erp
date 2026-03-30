<?php

declare(strict_types=1);

namespace App\Actions\PaymentMethod;

use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class DeletePaymentMethodAction
{
    public function handle(PaymentMethod $paymentMethod): void
    {
        if ($paymentMethod->payments()->exists() || $paymentMethod->expenses()->exists()) {
            throw ValidationException::withMessages([
                'payment_method' => 'This payment method is already used in financial records and cannot be deleted.',
            ]);
        }

        DB::transaction(function () use ($paymentMethod): void {
            $paymentMethod->delete();
        });
    }
}
