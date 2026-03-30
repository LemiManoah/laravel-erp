<?php

declare(strict_types=1);

namespace App\Actions\Invoice;

use App\Models\Invoice;
use Illuminate\Validation\ValidationException;

final readonly class IssueInvoiceAction
{
    public function __construct(
        private RefreshInvoiceStatusAction $refreshInvoiceStatus,
    ) {}

    public function handle(Invoice $invoice): Invoice
    {
        if ($invoice->status !== 'draft') {
            throw ValidationException::withMessages([
                'invoice' => 'Only draft invoices can be issued.',
            ]);
        }

        if ($invoice->items()->count() === 0) {
            throw ValidationException::withMessages([
                'invoice' => 'An invoice must have at least one item before it can be issued.',
            ]);
        }

        $invoice->forceFill([
            'issued_at' => now(),
            'status' => 'issued',
        ])->save();

        $this->refreshInvoiceStatus->handle($invoice);

        return $invoice->refresh();
    }
}
