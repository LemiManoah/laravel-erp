<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Payments</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Review recorded payments and their receipts.</p>
        </div>
        @can('viewAny', \App\Models\PaymentMethod::class)
            <a href="{{ route('payment-methods.index') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 sm:w-auto">
                <i class="fas fa-wallet mr-2 text-gray-400"></i> Payment Methods
            </a>
        @endcan
    </div>

    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search receipt, invoice, customer, or reference"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white md:col-span-2"
            >
            <button type="button" wire:click="clearSearch" class="rounded-md bg-gray-900 px-4 py-2 text-sm text-white transition hover:bg-gray-700">
                Clear
            </button>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Invoice</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Receipt</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    @forelse($payments as $payment)
                        <tr wire:key="payment-row-{{ $payment->id }}">
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $payment->payment_date->format('M d, Y') }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $payment->invoice->customer->full_name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-blue-600 dark:text-blue-400">
                                <a href="{{ route('invoices.show', $payment->invoice) }}">{{ $payment->invoice->invoice_number }}</a>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-blue-600 dark:text-blue-400">
                                @if($payment->receipt)
                                    <a href="{{ route('receipts.show', $payment->receipt) }}">{{ $payment->receipt->receipt_number }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ ucfirst($payment->status) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-mono {{ $payment->status === 'valid' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $currencyFormatter->formatValue($payment->amount, 2, $payment->currency) }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                <div class="flex flex-wrap gap-2">
                                    @can('view', $payment)
                                        <a href="{{ route('payments.show', $payment) }}"
                                            class="inline-flex items-center rounded-md border border-slate-200 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                                            View
                                        </a>
                                    @endcan
                                    @if($payment->receipt)
                                        @can('view', $payment->receipt)
                                            <a href="{{ route('receipts.show', $payment->receipt) }}"
                                                class="inline-flex items-center rounded-md border border-blue-200 px-2.5 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-50 dark:border-blue-800 dark:text-blue-300 dark:hover:bg-blue-900/30">
                                                Receipt
                                            </a>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No payments recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
