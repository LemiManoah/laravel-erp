<?php

declare(strict_types=1);

namespace App\Actions\Expense;

use App\Models\Expense;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final readonly class VoidExpenseAction
{
    public function handle(Expense $expense, string $reason): Expense
    {
        if ($expense->isVoided()) {
            throw ValidationException::withMessages([
                'expense' => 'This expense has already been voided.',
            ]);
        }

        $expense->forceFill([
            'status' => 'voided',
            'voided_at' => now(),
            'voided_by' => Auth::id(),
            'void_reason' => $reason,
        ])->save();

        return $expense->refresh();
    }
}
