<div>
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <a href="{{ route('reports.index') }}" class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400 mb-2 inline-block">
                <i class="fas fa-arrow-left mr-1"></i> Back to Reports
            </a>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Stock Card</h1>
        </div>

        <form action="{{ route('reports.stock-card') }}" method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-[10px] uppercase font-bold text-gray-500 mb-1">Product</label>
                <select name="product_id" class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:text-white">
                    <option value="">Select product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" @selected($selected_product?->id === $product->id)>{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] uppercase font-bold text-gray-500 mb-1">Location</label>
                <select name="location_id" class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:text-white">
                    <option value="">All locations</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" @selected($selected_location_id === $location->id)>{{ $location->name }}</option>
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
            <a href="{{ route('reports.stock-card.print', request()->only('product_id', 'location_id', 'start_date', 'end_date')) }}" class="px-4 py-1.5 bg-gray-700 text-white rounded text-sm hover:bg-gray-800 transition" target="_blank">Print</a>
        </form>
    </div>

    @if($selected_product)
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Product</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $selected_product->name }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">On Hand</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format((float) $summary['current_quantity'], 2) }} {{ $selected_product->baseUnit?->abbreviation }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Default Selling Price</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $selected_product->base_price === null ? 'N/A' : $currencyFormatter->formatValue($selected_product->base_price, 2) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Default Buying Price</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $selected_product->buying_price === null ? 'N/A' : $currencyFormatter->formatValue($selected_product->buying_price, 2) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Qty In Period</p>
                <p class="text-lg font-bold text-green-600">{{ number_format((float) $summary['quantity_in'], 2) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Qty Out Period</p>
                <p class="text-lg font-bold text-red-600">{{ number_format((float) $summary['quantity_out'], 2) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Stock Rows</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $stock_rows->count() }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 mb-6">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Current Stock Rows</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Row</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">On Hand</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($stock_rows as $stock)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $stock->location?->name ?? 'Unassigned' }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $stock->batch_number ?? 'Standard stock row' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $stock->expiry_date?->format('M d, Y') ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-right text-gray-900 dark:text-white">{{ number_format((float) $stock->quantity_on_hand, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-right text-gray-500 dark:text-gray-400">{{ $stock->unit_cost === null ? 'N/A' : $currencyFormatter->formatValue($stock->unit_cost, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No stock rows found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Row</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($movements as $movement)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $movement->movement_date->format('M d, Y H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $movement->movement_type->label() }}
                                    <span class="ml-2 text-xs {{ $movement->direction->value === 'in' ? 'text-green-600' : 'text-red-600' }}">{{ $movement->direction->label() }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $movement->location?->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $movement->inventoryStock?->batch_number ?? 'Standard stock row' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white">{{ number_format((float) $movement->quantity, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white">{{ number_format((float) $movement->balance_after, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $movement->notes ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No stock card entries found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="rounded-lg border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
            Choose a stock-tracked product to view its stock card.
        </div>
    @endif
</div>
