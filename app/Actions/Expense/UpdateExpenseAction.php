<?php

declare(strict_types=1);

namespace App\Actions\Expense;

use App\Models\Expense;
use App\Models\PaymentMethod;
use Illuminate\Validation\ValidationException;

final readonly class UpdateExpenseAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Expense $expense, array $data): Expense
    {
        if ($expense->isVoided()) {
            throw ValidationException::withMessages([
                'expense' => 'Voided expenses cannot be edited.',
            ]);
        }

        $paymentMethod = PaymentMethod::query()->findOrFail($data['payment_method_id']);

        $expense->update([
            ...$data,
            'payment_method' => $paymentMethod->name,
        ]);

        return $expense;
    }
}
