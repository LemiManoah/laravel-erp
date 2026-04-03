<div>
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <a href="{{ route('invoices.index') }}" class="mb-2 inline-block text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
                <i class="fas fa-arrow-left mr-1"></i> Back to Invoices
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</h1>
                <span @class([
                    'rounded-full px-2 py-1 text-xs font-medium',
                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' => $invoice->status === 'draft',
                    'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' => $invoice->status === 'issued',
                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' => $invoice->status === 'paid',
                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' => $invoice->status === 'partially_paid',
                    'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' => $invoice->status === 'overdue',
                    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' => $invoice->status === 'cancelled',
                ])>
                    {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                </span>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('issue', $invoice)
                <button type="button" wire:click="issue" wire:confirm="Issue this invoice? It will no longer be editable." class="rounded-md bg-blue-600 px-4 py-2 text-white transition hover:bg-blue-700">
                    <i class="fas fa-paper-plane mr-2"></i> Issue Invoice
                </button>
            @endcan

            @can('update', $invoice)
                <a href="{{ route('invoices.edit', $invoice) }}" class="rounded-md bg-yellow-600 px-4 py-2 text-white transition hover:bg-yellow-700">
                    <i class="fas fa-edit mr-2"></i> Edit
                </a>
            @endcan

            @can('create', [\App\Models\Payment::class, $invoice])
                @if($paymentMethods->isNotEmpty())
                    <button type="button" wire:click="$set('showPaymentForm', true)" class="rounded-md bg-green-600 px-4 py-2 text-white transition hover:bg-green-700">
                        <i class="fas fa-money-bill-wave mr-2"></i> Record Payment
                    </button>
                @else
                    <span class="inline-flex items-center rounded-md bg-amber-100 px-4 py-2 text-sm text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                        No active payment methods configured
                    </span>
                @endif
            @endcan

            @can('print', $invoice)
                <a href="{{ route('invoices.print', $invoice) }}" target="_blank" rel="noopener" class="rounded-md bg-gray-600 px-4 py-2 text-white transition hover:bg-gray-700">
                    <i class="fas fa-print mr-2"></i> Print
                </a>
            @endcan

            @can('cancel', $invoice)
                <button type="button" wire:click="$set('showCancelForm', true)" class="rounded-md bg-red-600 px-4 py-2 text-white transition hover:bg-red-700">
                    <i class="fas fa-times mr-2"></i> Cancel
                </button>
            @endcan
        </div>
    </div>

    {{-- Inline Record Payment Form --}}
    @if($showPaymentForm)
        <div class="mb-6 rounded-lg border border-green-200 bg-white p-6 shadow-sm dark:border-green-700 dark:bg-gray-800">
            <div class="mb-4 border-b border-gray-100 pb-4 dark:border-gray-700">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Record Payment</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $invoice->invoice_number }} &bull; Balance Due: {{ $currencyFormatter->formatValue($invoice->balance_due, 2, $invoice->currency) }}
                </p>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Amount <span class="text-red-500">*</span></label>
                    <input type="number" wire:model.live="payment_amount" step="0.01" min="0.01" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error('payment_amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Date <span class="text-red-500">*</span></label>
                    <input type="date" wire:model.blur="payment_date" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error('payment_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Currency <span class="text-red-500">*</span></label>
                    <select wire:model.live="payment_currency_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @foreach($currencies as $currency)
                            <option value="{{ $currency->id }}">{{ $currency->code }} - {{ $currency->name }}</option>
                        @endforeach
                    </select>
                    @error('payment_currency_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method <span class="text-red-500">*</span></label>
                    <select wire:model.live="payment_method_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Select Method</option>
                        @foreach($paymentMethods as $method)
                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                        @endforeach
                    </select>
                    @error('payment_method_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Reference Number</label>
                    <input type="text" wire:model.blur="payment_reference_number" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error('payment_reference_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                    <input type="text" wire:model.blur="payment_notes" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error('payment_notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="mt-4 flex gap-3">
                <button type="button" wire:click="recordPayment" class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700">
                    Record Payment
                </button>
                <button type="button" wire:click="$set('showPaymentForm', false)" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                    Cancel
                </button>
            </div>
        </div>
    @endif

    {{-- Inline Cancel Form --}}
    @if($showCancelForm)
        <div class="mb-6 rounded-lg border border-red-200 bg-white p-6 shadow-sm dark:border-red-700 dark:bg-gray-800">
            <h2 class="mb-2 text-lg font-medium text-gray-900 dark:text-white">Cancel Invoice {{ $invoice->invoice_number }}</h2>
            <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">This action cannot be undone.</p>
            <div class="mb-4">
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Reason for Cancellation <span class="text-red-500">*</span></label>
                <textarea wire:model.blur="cancellation_reason" rows="3" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                @error('cancellation_reason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-3">
                <button type="button" wire:click="cancelInvoice" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
                    Confirm Cancellation
                </button>
                <button type="button" wire:click="$set('showCancelForm', false)" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                    Back
                </button>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main Invoice Content --}}
        <div class="space-y-6 lg:col-span-2">
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-start justify-between border-b border-gray-100 p-6 dark:border-gray-700">
                    <div>
                        <h2 class="text-lg font-bold uppercase tracking-wider text-gray-900 dark:text-white">Bill To:</h2>
                        <p class="mt-2 font-semibold text-gray-900 dark:text-white">{{ $invoice->customer->full_name }}</p>
                        <p class="text-gray-500 dark:text-gray-400">{{ $invoice->customer->phone }}</p>
                        @if($invoice->customer->address)
                            <p class="max-w-xs text-gray-500 dark:text-gray-400">{{ $invoice->customer->address }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-gray-500 dark:text-gray-400">Date: <span class="font-semibold text-gray-900 dark:text-white">{{ $invoice->invoice_date->format('M d, Y') }}</span></p>
                        @if($invoice->due_date)
                            <p class="text-gray-500 dark:text-gray-400">Due: <span class="font-semibold text-gray-900 dark:text-white">{{ $invoice->due_date->format('M d, Y') }}</span></p>
                        @endif
                        @if($invoice->order)
                            <p class="text-gray-500 dark:text-gray-400">Order: <span class="font-semibold text-gray-900 dark:text-white">{{ $invoice->order->order_number }}</span></p>
                        @endif
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Description</th>
                                <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Qty</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Price</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            @foreach($invoice->items as $item)
                                <tr>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $item->item_name }}</p>
                                        @if($item->description)
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item->description }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4 text-right font-mono text-sm text-gray-500 dark:text-gray-400">
                                        {{ $currencyFormatter->formatValue($item->unit_price, 2, $invoice->currency) }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-mono text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ $currencyFormatter->formatValue($item->line_total, 2, $invoice->currency) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <td colspan="3" class="px-6 py-2 text-right text-sm font-medium uppercase text-gray-500 dark:text-gray-400">Subtotal</td>
                                <td class="px-6 py-2 text-right font-mono text-sm font-bold text-gray-900 dark:text-white">{{ $currencyFormatter->formatValue($invoice->subtotal_amount, 2, $invoice->currency) }}</td>
                            </tr>
                            @if($invoice->discount_amount > 0)
                                <tr>
                                    <td colspan="3" class="px-6 py-2 text-right text-sm font-medium uppercase text-gray-500 dark:text-gray-400">Discount</td>
                                    <td class="px-6 py-2 text-right font-mono text-sm font-bold text-red-600">-{{ $currencyFormatter->formatValue($invoice->discount_amount, 2, $invoice->currency) }}</td>
                                </tr>
                            @endif
                            @if($invoice->tax_amount > 0)
                                <tr>
                                    <td colspan="3" class="px-6 py-2 text-right text-sm font-medium uppercase text-gray-500 dark:text-gray-400">Tax</td>
                                    <td class="px-6 py-2 text-right font-mono text-sm font-bold text-gray-900 dark:text-white">{{ $currencyFormatter->formatValue($invoice->tax_amount, 2, $invoice->currency) }}</td>
                                </tr>
                            @endif
                            <tr class="bg-gray-100 dark:bg-gray-700">
                                <td colspan="3" class="px-6 py-3 text-right text-base font-bold uppercase text-gray-900 dark:text-white">Grand Total</td>
                                <td class="px-6 py-3 text-right font-mono text-base font-bold text-gray-900 dark:text-white">{{ $currencyFormatter->formatValue($invoice->total_amount, 2, $invoice->currency) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($invoice->notes)
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-900/30">
                    <h3 class="mb-2 text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Notes:</h3>
                    <p class="whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">{{ $invoice->notes }}</p>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6 lg:col-span-1">
            {{-- Payment Summary --}}
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-bold uppercase tracking-wider text-gray-900 dark:text-white">Payment Summary</h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Total Invoiced</span>
                        <span class="font-mono font-bold text-gray-900 dark:text-white">{{ $currencyFormatter->formatValue($invoice->total_amount, 2, $invoice->currency) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Total Paid</span>
                        <span class="font-mono font-bold text-green-600">{{ $currencyFormatter->formatValue($invoice->amount_paid, 2, $invoice->currency) }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-gray-100 pt-4 dark:border-gray-700">
                        <span class="text-lg font-bold text-gray-900 dark:text-white">Balance Due</span>
                        <span class="font-mono text-xl font-black text-red-600">{{ $currencyFormatter->formatValue($invoice->balance_due, 2, $invoice->currency) }}</span>
                    </div>
                </div>
            </div>

            {{-- Payment History --}}
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-100 p-6 dark:border-gray-700">
                    <h2 class="text-lg font-bold uppercase tracking-wider text-gray-900 dark:text-white">Payment History</h2>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($invoice->payments as $payment)
                        <div class="p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $payment->payment_date->format('M d, Y') }}</p>
                                    <p class="text-xs uppercase tracking-tighter text-gray-500 dark:text-gray-400">{{ $payment->paymentMethodDefinition?->name ?? '—' }} &bull; {{ $payment->reference_number ?? 'No Ref' }}</p>
                                    @if($payment->receipt)
                                        @can('view', $payment->receipt)
                                            <a href="{{ route('receipts.show', $payment->receipt) }}" class="text-xs text-blue-600 dark:text-blue-400">{{ $payment->receipt->receipt_number }}</a>
                                        @endcan
                                    @endif
                                </div>
                                <div class="text-right">
                                    <p class="font-mono text-sm font-bold {{ $payment->status === 'valid' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $currencyFormatter->formatValue($payment->amount, 2, $payment->currency) }}
                                    </p>
                                    @if($payment->status === 'voided')
                                        <span class="inline-block rounded bg-red-100 px-1 text-[10px] text-red-600">VOIDED</span>
                                    @else
                                        @can('void', $payment)
                                            <button type="button" wire:click="$set('voidingPaymentId', {{ $payment->id }})" class="mt-1 text-[11px] text-red-600 hover:text-red-800">
                                                Void payment
                                            </button>
                                        @endcan
                                    @endif
                                </div>
                            </div>

                            {{-- Inline Void Form --}}
                            @if($voidingPaymentId === $payment->id)
                                <div class="mt-3 rounded border border-red-200 bg-red-50 p-3 dark:border-red-700 dark:bg-red-900/20">
                                    <p class="mb-2 text-sm font-medium text-red-800 dark:text-red-300">Void this payment?</p>
                                    <textarea wire:model.blur="void_reason" rows="2" placeholder="Reason (required)" class="w-full rounded border border-red-300 px-2 py-1 text-sm dark:border-red-600 dark:bg-gray-700 dark:text-white"></textarea>
                                    @error('void_reason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    <div class="mt-2 flex gap-2">
                                        <button type="button" wire:click="voidPayment" class="rounded bg-red-600 px-3 py-1 text-xs font-semibold text-white hover:bg-red-700">Confirm Void</button>
                                        <button type="button" wire:click="$set('voidingPaymentId', null)" class="rounded border border-gray-300 px-3 py-1 text-xs text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300">Cancel</button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="p-6 text-center text-sm text-gray-500 dark:text-gray-400">No payments recorded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
