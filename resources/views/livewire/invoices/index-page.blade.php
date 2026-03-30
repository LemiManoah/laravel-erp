<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Invoices</h1>
        @can('create', \App\Models\Invoice::class)
            <a href="{{ route('invoices.create') }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm text-white transition hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i> Create Invoice
            </a>
        @endcan
    </div>

    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search invoice number or customer"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
            <select wire:model.live="status" class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">All statuses</option>
                @foreach(['draft', 'issued', 'partially_paid', 'paid', 'overdue', 'cancelled'] as $filterStatus)
                    <option value="{{ $filterStatus }}">{{ ucfirst(str_replace('_', ' ', $filterStatus)) }}</option>
                @endforeach
            </select>
            <button type="button" wire:click="clearFilters" class="rounded-md bg-gray-900 px-4 py-2 text-sm text-white transition hover:bg-gray-700">
                Clear Filters
            </button>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Inv #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    @forelse($invoices as $invoice)
                        <tr wire:key="invoice-row-{{ $invoice->id }}">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-blue-600 dark:text-blue-400">
                                <a href="{{ route('invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">
                                {{ $invoice->customer->full_name }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $invoice->invoice_date->format('M d, Y') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $currencyFormatter->formatValue($invoice->total_amount, 2, $invoice->currency) }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span @class([
                                    'px-2 py-1 text-xs rounded-full font-medium',
                                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' => $invoice->status === 'draft',
                                    'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' => $invoice->status === 'issued',
                                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' => $invoice->status === 'paid',
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' => $invoice->status === 'partially_paid',
                                    'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' => $invoice->status === 'overdue',
                                    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' => $invoice->status === 'cancelled',
                                ])>
                                    {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                <div class="flex flex-wrap gap-2">
                                    @can('view', $invoice)
                                        <a href="{{ route('invoices.show', $invoice) }}"
                                            class="inline-flex items-center rounded-md border border-slate-200 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                                            View
                                        </a>
                                    @endcan
                                    @can('print', $invoice)
                                        <a href="{{ route('invoices.print', $invoice) }}"
                                            target="_blank" rel="noopener"
                                            class="inline-flex items-center rounded-md border border-blue-200 px-2.5 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-50 dark:border-blue-800 dark:text-blue-300 dark:hover:bg-blue-900/30">
                                            Print
                                        </a>
                                    @endcan
                                    @can('update', $invoice)
                                        @if($invoice->status === 'draft')
                                            <a href="{{ route('invoices.edit', $invoice) }}"
                                                class="inline-flex items-center rounded-md border border-amber-200 px-2.5 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-50 dark:border-amber-800 dark:text-amber-300 dark:hover:bg-amber-900/30">
                                                Edit
                                            </a>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No invoices found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
            <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
