<x-layouts.app title="Supplier Purchasing Report">
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <a href="{{ route('reports.index') }}" class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400 mb-2 inline-block">
                <i class="fas fa-arrow-left mr-1"></i> Back to Reports
            </a>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Supplier Purchasing Report</h1>
        </div>

        <form action="{{ route('reports.supplier-purchasing') }}" method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-[10px] uppercase font-bold text-gray-500 mb-1">Supplier</label>
                <select name="supplier_id" class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:text-white">
                    <option value="">All suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected($selected_supplier_id === $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] uppercase font-bold text-gray-500 mb-1">From</label>
                <input type="date" name="start_date" value="{{ $start_date }}" class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-[10px] uppercase font-bold text-gray-500 mb-1">To</label>
                <input type="date" name="end_date" value="{{ $end_date }}" class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:text-white">
            </div>
            <button type="submit" class="px-4 py-1.5 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 transition">Filter</button>
            <a href="{{ route('reports.supplier-purchasing.print', request()->only('supplier_id', 'start_date', 'end_date')) }}" class="px-4 py-1.5 bg-gray-700 text-white rounded text-sm hover:bg-gray-800 transition" target="_blank">Print</a>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Suppliers</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $summary['suppliers_count'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Ordered Value</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $currencyFormatter->formatValue($summary['ordered_amount'], 2) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Received Value</p>
            <p class="text-xl font-bold text-green-600">{{ $currencyFormatter->formatValue($summary['received_amount'], 2) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Returned Value</p>
            <p class="text-xl font-bold text-red-600">{{ $currencyFormatter->formatValue($summary['returned_amount'], 2) }}</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 mb-6">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Supplier Summary</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Receipts</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Returns</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ordered</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Received</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Returned</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Net Purchased</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($supplier_rows as $row)
                        <tr>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $row['supplier']->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $row['supplier']->code ?: 'No supplier code' }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-right text-gray-500 dark:text-gray-400">{{ $row['orders_count'] }}</td>
                            <td class="px-6 py-4 text-sm text-right text-gray-500 dark:text-gray-400">{{ $row['receipts_count'] }}</td>
                            <td class="px-6 py-4 text-sm text-right text-gray-500 dark:text-gray-400">{{ $row['returns_count'] }}</td>
                            <td class="px-6 py-4 text-sm text-right text-gray-900 dark:text-white font-mono">{{ $currencyFormatter->formatValue($row['ordered_amount'], 2) }}</td>
                            <td class="px-6 py-4 text-sm text-right text-green-600 font-mono">{{ $currencyFormatter->formatValue($row['received_amount'], 2) }}</td>
                            <td class="px-6 py-4 text-sm text-right text-red-600 font-mono">{{ $currencyFormatter->formatValue($row['returned_amount'], 2) }}</td>
                            <td class="px-6 py-4 text-sm text-right font-mono font-bold text-gray-900 dark:text-white">{{ $currencyFormatter->formatValue($row['net_purchased_amount'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No supplier purchasing activity found for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Purchase Receipts</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($recent_receipts as $receipt)
                            <tr>
                                <td class="px-6 py-4 text-sm text-blue-600 dark:text-blue-400">
                                    <a href="{{ route('purchase-receipts.show', $receipt) }}">{{ $receipt->receipt_number }}</a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $receipt->supplier->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $receipt->receipt_date->format('M d, Y') }}</td>
                                <td class="px-6 py-4 text-sm text-right text-green-600 font-mono">{{ $currencyFormatter->formatValue($receipt->subtotal_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No receipts found for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Purchase Returns</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Return</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($recent_returns as $purchaseReturn)
                            <tr>
                                <td class="px-6 py-4 text-sm text-blue-600 dark:text-blue-400">
                                    <a href="{{ route('purchase-returns.show', $purchaseReturn) }}">{{ $purchaseReturn->return_number }}</a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $purchaseReturn->supplier->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $purchaseReturn->return_date->format('M d, Y') }}</td>
                                <td class="px-6 py-4 text-sm text-right text-red-600 font-mono">{{ $currencyFormatter->formatValue($purchaseReturn->subtotal_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No returns found for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
