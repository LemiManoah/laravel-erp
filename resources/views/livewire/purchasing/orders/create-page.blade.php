<div>
    <div class="mb-6">
        <a href="{{ route('purchase-orders.index') }}" class="mb-2 inline-block text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
            <i class="fas fa-arrow-left mr-1"></i> Back to Purchase Orders
        </a>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Create New Purchase Order</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Plan what should be ordered from a supplier before the stock arrives.</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-300">
            Please correct the highlighted purchase order form errors and try again.
        </div>
    @endif

    <form wire:submit="save">
        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Settings</h2>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Order Number *</label>
                        <input type="text" wire:model.blur="order_number" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @error('order_number') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Supplier *</label>
                        <select wire:model.live="supplier_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">Select supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Order Date *</label>
                        <input type="date" wire:model.live="order_date" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @error('order_date') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Expected Date</label>
                        <input type="date" wire:model.live="expected_date" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @error('expected_date') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Stock Location *</label>
                        <select wire:model.live="stock_location_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">Select location</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->name }}</option>
                            @endforeach
                        </select>
                        @error('stock_location_id') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Status *</label>
                        <select wire:model.live="status" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            @foreach($statuses as $statusOption)
                                <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                            @endforeach
                        </select>
                        @error('status') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">Purchase Order Items</h2>
                    @error('items')
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="w-56 px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Inventory Item</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Line Notes</th>
                                <th class="w-28 px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Qty</th>
                                <th class="w-36 px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Unit Cost</th>
                                <th class="w-36 px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Total</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($items as $index => $item)
                                <tr>
                                    <td class="px-4 py-2">
                                        <select wire:model.live="items.{{ $index }}.inventory_item_id" class="w-full rounded-md border border-gray-300 px-3 py-1 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                            <option value="">Select inventory item</option>
                                            @foreach($products as $productOption)
                                                <option value="{{ $productOption->id }}">
                                                    {{ $productOption->sku ? $productOption->name.' ('.$productOption->sku.')' : $productOption->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("items.$index.inventory_item_id") <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="text" wire:model.live.debounce.300ms="items.{{ $index }}.notes" class="w-full rounded-md border border-gray-300 px-3 py-1 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                        @error("items.$index.notes") <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" step="0.01" min="0.01" wire:model.live="items.{{ $index }}.quantity" class="w-full rounded-md border border-gray-300 px-3 py-1 text-center text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                        @error("items.$index.quantity") <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" step="0.01" min="0" wire:model.live="items.{{ $index }}.unit_cost" class="w-full rounded-md border border-gray-300 px-3 py-1 text-right text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                        @error("items.$index.unit_cost") <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-4 py-2 text-right text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $currencyFormatter->formatValue(((float) ($item['quantity'] ?: 0)) * ((float) ($item['unit_cost'] ?: 0)), 2) }}
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        <button type="button" wire:click="removeItem({{ $index }})" class="p-1 text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" title="Remove item">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <button type="button" wire:click="addItem" class="mt-4 rounded bg-gray-100 px-3 py-1 text-sm text-gray-700 transition hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">
                    <i class="fas fa-plus mr-1"></i> Add Item
                </button>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Order Notes</label>
                    <textarea wire:model.live.debounce.300ms="notes" rows="4" placeholder="Supplier instructions, delivery notes, terms..." class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                    @error('notes') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Summary</h2>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-gray-500 dark:text-gray-400">
                            <span>Items</span>
                            <span>{{ count($items) }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-100 pb-3 text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            <span>Total</span>
                            <span>{{ $currencyFormatter->formatValue($total, 2) }}</span>
                        </div>
                        <div class="flex justify-between pt-1 text-lg font-bold text-gray-900 dark:text-white">
                            <span>Order Value</span>
                            <span>{{ $currencyFormatter->formatValue($total, 2) }}</span>
                        </div>
                    </div>

                    <button type="submit" class="mt-6 w-full rounded-md bg-blue-600 px-4 py-2 font-semibold text-white transition hover:bg-blue-700">
                        Save Purchase Order
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
