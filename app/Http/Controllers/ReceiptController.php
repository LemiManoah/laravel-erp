<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

final readonly class ReceiptController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:receipts.view'),
        ];
    }

    public function print(Receipt $receipt): View
    {
        $this->authorize('print', $receipt);

        $receipt->load(['payment.invoice.customer', 'payment.receiver', 'payment.currency']);

        return view('receipts.print', compact('receipt'));
    }
}
