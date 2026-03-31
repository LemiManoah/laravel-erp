<div>
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <a href="{{ route('purchase-orders.index') }}" class="mb-2 inline-block text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
                <i class="fas fa-arrow-left mr-1"></i> Back to Purchase Orders
            </a>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $order->order_number }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Created on {{ $order->order_date?->format('M d, Y') }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('purchase-orders.create') }}" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i> New Order
            </a>
            <a href="{{ route('purchase-receipts.create', ['order' => $order->id]) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                Create Receipt
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800 lg:col-span-1">
            <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Order Summary</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Supplier</dt><dd class="text-right text-gray-900 dark:text-white">{{ $order->supplier?->name }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Location</dt><dd class="text-right text-gray-900 dark:text-white">{{ $order->stockLocation?->name }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Status</dt><dd class="text-right text-gray-900 dark:text-white">{{ $order->status->label() }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Expected Date</dt><dd class="text-right text-gray-900 dark:text-white">{{ $order->expected_date?->format('M d, Y') ?? 'N/A' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Total</dt><dd class="text-right font-semibold text-gray-900 dark:text-white">{{ $currencyFormatter->formatValue($order->subtotal_amount, 2) }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Created By</dt><dd class="text-right text-gray-900 dark:text-white">{{ $order->creator?->name ?? 'System' }}</dd></div>
            </dl>
            @if($order->notes)
                <div class="mt-6 border-t border-gray-200 pt-4 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300">{{ $order->notes }}</div>
            @endif
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 lg:col-span-2">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Order Items</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Product</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Quantity</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Unit Cost</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Line Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($order->items as $item)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $item->product?->name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $item->product?->baseUnit?->abbreviation ?: 'No unit' }}</div>
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-gray-900 dark:text-white">{{ number_format((float) $item->quantity, 2) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-900 dark:text-white">{{ $currencyFormatter->formatValue($item->unit_cost, 2) }}</td>
                                <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900 dark:text-white">{{ $currencyFormatter->formatValue($item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
