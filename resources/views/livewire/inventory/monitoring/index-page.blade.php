<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Inventory Monitoring</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Track low stock products and expiry-sensitive stock rows from one place.</p>
        </div>
        <div class="flex gap-2">
            @can('inventory-movements.create')
                <a href="{{ route('inventory.receipts.create') }}" class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i> Receive Stock
                </a>
            @endcan
            @can('inventory-stocks.view')
                <a href="{{ route('inventory.stocks.index') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    <i class="fas fa-layer-group mr-2"></i> View Stock Rows
                </a>
            @endcan
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Tracked Products</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $summary['tracked_products'] }}</p>
        </div>
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 shadow-sm dark:border-amber-800 dark:bg-amber-900/20">
            <p class="text-sm text-amber-700 dark:text-amber-300">Low Stock</p>
            <p class="mt-2 text-2xl font-semibold text-amber-900 dark:text-amber-100">{{ $summary['low_stock_products'] }}</p>
        </div>
        <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 shadow-sm dark:border-yellow-800 dark:bg-yellow-900/20">
            <p class="text-sm text-yellow-700 dark:text-yellow-300">Near Expiry</p>
            <p class="mt-2 text-2xl font-semibold text-yellow-900 dark:text-yellow-100">{{ $summary['near_expiry_rows'] }}</p>
        </div>
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 shadow-sm dark:border-red-800 dark:bg-red-900/20">
            <p class="text-sm text-red-700 dark:text-red-300">Expired Stock Rows</p>
            <p class="mt-2 text-2xl font-semibold text-red-900 dark:text-red-100">{{ $summary['expired_rows'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Low Stock Products</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">On Hand</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Reorder Level</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($lowStockProducts as $product)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $product->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ number_format((float) $product->quantity_on_hand, 2) }} {{ $product->baseUnit?->abbreviation }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ number_format((float) $product->reorder_level, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No low stock products right now.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Near Expiry</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Stock Row</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Expiry</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($nearExpiryStocks as $stock)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $stock->product->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $stock->batch_number ?? 'Standard stock row' }} @if($stock->location) ({{ $stock->location->name }}) @endif</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $stock->expiry_date?->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No near-expiry stock rows.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Expired Stock Rows</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Stock Row</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">On Hand</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Expiry</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($expiredStocks as $stock)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $stock->product->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $stock->batch_number ?? 'Standard stock row' }} @if($stock->location) ({{ $stock->location->name }}) @endif</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ number_format((float) $stock->quantity_on_hand, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-red-600 dark:text-red-300">{{ $stock->expiry_date?->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No expired stock rows.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
