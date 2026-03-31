<?php

declare(strict_types=1);

namespace App\Actions\Invoice;

use App\Actions\Inventory\ReverseInvoiceInventoryAction;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class CancelInvoiceAction
{
    public function __construct(
        private ReverseInvoiceInventoryAction $reverseInvoiceInventory,
    ) {}

    public function handle(Invoice $invoice, string $reason): Invoice
    {
        if (! $invoice->canBeCancelled()) {
            throw ValidationException::withMessages([
                'invoice' => 'This invoice cannot be cancelled once valid payments exist.',
            ]);
        }

        DB::transaction(function () use ($invoice, $reason): void {
            $invoice->forceFill([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => Auth::id(),
                'cancellation_reason' => $reason,
            ])->save();

            $this->reverseInvoiceInventory->handle($invoice);
        });

        return $invoice->refresh();
    }
}
