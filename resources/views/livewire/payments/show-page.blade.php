<div>
    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/50 dark:bg-green-900/20 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <a href="{{ route('payments.index') }}" class="mb-2 inline-block text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
                <i class="fas fa-arrow-left mr-1"></i> Back to Payments
            </a>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Payment Details</h1>
        </div>
        @can('void', $payment)
            <button type="button" wire:click="$toggle('showVoidForm')" class="rounded-md bg-red-600 px-4 py-2 text-white transition hover:bg-red-700">
                <i class="fas fa-ban mr-2"></i> Void Payment
            </button>
        @endcan
    </div>

    @if($showVoidForm)
        <div class="mb-6 rounded-lg border border-red-200 bg-white p-6 shadow-sm dark:border-red-700 dark:bg-gray-800">
            <h2 class="mb-2 text-lg font-medium text-gray-900 dark:text-white">Void Payment</h2>
            <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">This action cannot be undone.</p>
            <div class="mb-4">
                <label for="void_reason" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Reason <span class="text-red-500">*</span></label>
                <textarea id="void_reason" rows="3" wire:model.blur="void_reason" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                @error('void_reason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                @error('payment') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-3">
                <button type="button" wire:click="voidPayment" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">Confirm Void</button>
                <button type="button" wire:click="$set('showVoidForm', false)" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">Cancel</button>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="space-y-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Customer</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $payment->invoice->customer->full_name }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Invoice</p>
                    <p class="text-lg font-semibold text-blue-600 dark:text-blue-400"><a href="{{ route('invoices.show', $payment->invoice) }}">{{ $payment->invoice->invoice_number }}</a></p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Method</p>
                    <p class="text-gray-900 dark:text-white">{{ $payment->paymentMethodDefinition?->name ?? $payment->payment_method }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Reference</p>
                    <p class="text-gray-900 dark:text-white">{{ $payment->reference_number ?: 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Received By</p>
                    <p class="text-gray-900 dark:text-white">{{ $payment->receiver?->name ?? 'N/A' }}</p>
                </div>
                @if($payment->notes)
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Notes</p>
                        <p class="whitespace-pre-line text-gray-900 dark:text-white">{{ $payment->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="space-y-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Payment Date</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $payment->payment_date->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Amount</p>
                    <p class="text-2xl font-bold {{ $payment->status === 'valid' ? 'text-green-600' : 'text-red-600' }}">{{ $currencyFormatter->formatValue($payment->amount, 2, $payment->currency) }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</p>
                    <p class="text-gray-900 dark:text-white">{{ ucfirst($payment->status) }}</p>
                </div>
                @if($payment->receipt)
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Receipt</p>
                        <p class="text-blue-600 dark:text-blue-400"><a href="{{ route('receipts.show', $payment->receipt) }}">{{ $payment->receipt->receipt_number }}</a></p>
                    </div>
                @endif
                @if($payment->voided_at)
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Voided</p>
                        <p class="text-gray-900 dark:text-white">{{ $payment->voided_at->format('M d, Y H:i') }} by {{ $payment->voider?->name ?? 'Unknown' }}</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $payment->void_reason }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
