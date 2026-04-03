<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

final readonly class InvoiceController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:invoices.print', only: ['print']),
        ];
    }

    public function print(Invoice $invoice): View
    {
        $this->authorize('print', $invoice);

        $invoice->load(['customer', 'items']);

        return view('invoices.print', compact('invoice'));
    }
}
