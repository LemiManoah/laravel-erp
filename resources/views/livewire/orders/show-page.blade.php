<div>
    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/50 dark:bg-green-900/20 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <a href="{{ route('orders.index') }}" class="mb-2 inline-block text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
                <i class="fas fa-arrow-left mr-1"></i> Back to Orders
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $order->order_number }}</h1>
                <span @class([
                    'rounded-full px-2 py-1 text-xs font-medium',
                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' => $order->status === 'draft',
                    'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' => $order->status === 'confirmed',
                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' => in_array($order->status, ['in_cutting', 'in_stitching', 'in_finishing', 'awaiting_fitting']),
                    'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' => $order->status === 'ready_for_delivery',
                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' => $order->status === 'delivered',
                    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' => $order->status === 'cancelled',
                ])>
                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                </span>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            @if(!$order->invoice)
                @can('create', \App\Models\Invoice::class)
                    <a href="{{ route('invoices.create', ['customer_id' => $order->customer_id, 'order_id' => $order->id]) }}" class="rounded-md bg-indigo-600 px-4 py-2 text-white transition hover:bg-indigo-700">
                        <i class="fas fa-file-invoice-dollar mr-2"></i> Generate Invoice
                    </a>
                @endcan
            @else
                @can('view', $order->invoice)
                    <a href="{{ route('invoices.show', $order->invoice) }}" class="rounded-md bg-gray-600 px-4 py-2 text-white transition hover:bg-gray-700">
                        <i class="fas fa-file-invoice mr-2"></i> View Invoice
                    </a>
                @endcan
            @endif

            @can('update', $order)
                <a href="{{ route('orders.edit', $order) }}" class="rounded-md bg-yellow-600 px-4 py-2 text-white transition hover:bg-yellow-700">
                    <i class="fas fa-edit mr-2"></i> Edit Order
                </a>
            @endcan

            @can('delete', $order)
                <button type="button" wire:click="delete" wire:confirm="Delete this order? This cannot be undone." class="rounded-md bg-red-600 px-4 py-2 text-white transition hover:bg-red-700">
                    <i class="fas fa-trash mr-2"></i> Delete
                </button>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main: Customer + Items --}}
        <div class="space-y-6 lg:col-span-2">
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-start justify-between border-b border-gray-100 p-6 dark:border-gray-700">
                    <div>
                        <h2 class="text-lg font-bold uppercase tracking-wider text-gray-900 dark:text-white">Customer Details</h2>
                        <p class="mt-2 font-semibold text-gray-900 dark:text-white">
                            <a href="{{ route('customers.show', $order->customer) }}" class="transition hover:text-blue-600">
                                {{ $order->customer->full_name }}
                            </a>
                        </p>
                        <p class="text-gray-500 dark:text-gray-400">{{ $order->customer->phone }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-gray-500 dark:text-gray-400">Order Date: <span class="font-semibold text-gray-900 dark:text-white">{{ $order->order_date->format('M d, Y') }}</span></p>
                        @if($order->promised_delivery_date)
                            <p class="text-gray-500 dark:text-gray-400">Promised: <span class="font-semibold text-gray-900 dark:text-white">{{ $order->promised_delivery_date->format('M d, Y') }}</span></p>
                        @endif
                        <p class="mt-2">
                            <span @class([
                                'rounded px-2 py-0.5 text-[10px] font-bold uppercase',
                                'bg-red-100 text-red-600' => in_array($order->priority, ['urgent', 'high']),
                                'bg-blue-100 text-blue-600' => $order->priority === 'medium',
                                'bg-gray-100 text-gray-600' => $order->priority === 'low',
                            ])>
                                {{ $order->priority }} Priority
                            </span>
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Garment Type</th>
                                <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Qty</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Style & Fabric Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $item->garment_type }}</td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4">
                                        @if($item->style_notes)
                                            <p class="text-sm text-gray-900 dark:text-white"><span class="text-xs font-bold uppercase text-gray-400">Style:</span> {{ $item->style_notes }}</p>
                                        @endif
                                        @if($item->fabric_details)
                                            <p class="text-sm text-gray-600 dark:text-gray-400"><span class="text-xs font-bold uppercase text-gray-400">Fabric:</span> {{ $item->fabric_details }}</p>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if($order->notes)
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-900/30">
                    <h3 class="mb-2 text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">General Order Notes:</h3>
                    <p class="whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">{{ $order->notes }}</p>
                </div>
            @endif
        </div>

        {{-- Sidebar: Workflow --}}
        <div class="lg:col-span-1">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-bold uppercase tracking-wider text-gray-900 dark:text-white">Workflow</h2>
                <div class="space-y-6">
                    <div>
                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Assignee</p>
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-600">
                                {{ substr($order->assignee->name ?? 'U', 0, 1) }}
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $order->assignee->name ?? 'Unassigned' }}</span>
                        </div>
                    </div>

                    <div>
                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Production Status</p>
                        @can('update', $order)
                            <select wire:model.live="status" wire:change="updateStatus" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                @foreach(['confirmed', 'in_cutting', 'in_stitching', 'in_finishing', 'awaiting_fitting', 'ready_for_delivery', 'delivered', 'cancelled'] as $s)
                                    <option value="{{ $s }}">{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                                @endforeach
                            </select>
                        @else
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</p>
                        @endcan
                    </div>

                    <div class="border-t border-gray-100 pt-4 dark:border-gray-700">
                        <p class="mb-1 text-[10px] font-bold uppercase text-gray-400">Created By</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ $order->creator->name ?? 'System' }} on {{ $order->created_at?->format('M d, Y') ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
