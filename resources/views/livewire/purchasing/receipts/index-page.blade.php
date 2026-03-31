<div>
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Purchase Receipts</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Track stock receipts from suppliers and see what has been posted into inventory.</p>
        </div>
        @can('purchase-receipts.create')
            <a href="{{ route('purchase-receipts.create') }}" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i> New Purchase Receipt
            </a>
        @endcan
    </div>

    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Receipt number, supplier, location..." class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Supplier</label>
                <select wire:model.live="supplier" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">All suppliers</option>
                    @foreach($suppliers as $supplierOption)
                        <option value="{{ $supplierOption->id }}">{{ $supplierOption->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="button" wire:click="clearFilters" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    Clear Filters
                </button>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Receipt</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Location</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Total</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($receipts as $receipt)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $receipt->receipt_number }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $receipt->receipt_date?->format('M d, Y') }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $receipt->supplier?->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $receipt->stockLocation?->name }}</td>
                            <td class="px-6 py-4 text-right text-sm font-mono text-gray-900 dark:text-white">{{ $currencyFormatter->formatValue($receipt->subtotal_amount, 2) }}</td>
                            <td class="px-6 py-4 text-right text-sm">
                                <a href="{{ route('purchase-receipts.show', $receipt) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No purchase receipts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-700">
            {{ $receipts->links() }}
        </div>
    </div>
</div>
