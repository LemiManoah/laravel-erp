<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('payments.show', $receipt->payment) }}" class="mb-2 inline-block text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
                <i class="fas fa-arrow-left mr-1"></i> Back to Payment
            </a>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $receipt->receipt_number }}</h1>
        </div>
        <a href="{{ route('receipts.print', $receipt) }}" target="_blank" rel="noopener" class="rounded-md bg-gray-700 px-4 py-2 text-white transition hover:bg-gray-800">
            <i class="fas fa-print mr-2"></i> Print Receipt
        </a>
    </div>

    <div class="mx-auto max-w-3xl overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-100 px-8 py-6 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Receipt Date</p>
            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $receipt->issued_date->format('M d, Y') }}</p>
        </div>
        <div class="space-y-4 px-8 py-6">
            <div class="flex justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Customer</p>
                    <p class="text-gray-900 dark:text-white">{{ $receipt->payment->invoice->customer->full_name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Invoice</p>
                    <p class="text-gray-900 dark:text-white">{{ $receipt->payment->invoice->invoice_number }}</p>
                </div>
            </div>
            <div class="flex justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Payment Method</p>
                    <p class="text-gray-900 dark:text-white">{{ $receipt->payment->payment_method }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Reference</p>
                    <p class="text-gray-900 dark:text-white">{{ $receipt->payment->reference_number ?: 'N/A' }}</p>
                </div>
            </div>
            <div class="flex items-center justify-between border-t border-gray-100 pt-4 dark:border-gray-700">
                <p class="text-base font-bold uppercase text-gray-900 dark:text-white">Amount Received</p>
                <p class="font-mono text-2xl font-black text-green-600">{{ $currencyFormatter->formatValue($receipt->payment->amount, 2, $receipt->payment->currency) }}</p>
            </div>
        </div>
    </div>
</div>
