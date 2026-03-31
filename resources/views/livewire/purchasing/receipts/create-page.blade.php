<div>
    <div class="mb-6">
        <a href="{{ route('purchase-receipts.index') }}" class="mb-2 inline-block text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
            <i class="fas fa-arrow-left mr-1"></i> Back to Purchase Receipts
        </a>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">New Purchase Receipt</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Post supplier-delivered stock directly into inventory.</p>
    </div>

    @if($selectedOrder)
        <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800 dark:border-blue-900/50 dark:bg-blue-900/20 dark:text-blue-200">
            Prefilled from purchase order <span class="font-semibold">{{ $selectedOrder->order_number }}</span> for {{ $selectedOrder->supplier?->name }}.
            Review batch and expiry details before posting stock.
        </div>
    @endif

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <form wire:submit="save">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Receipt Number <span class="text-red-500">*</span></label>
                    <input type="text" wire:model.blur="receipt_number" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error('receipt_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Receipt Date <span class="text-red-500">*</span></label>
                    <input type="date" wire:model="receipt_date" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error('receipt_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Supplier <span class="text-red-500">*</span></label>
                    <select wire:model.live="supplier_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" @disabled($selectedOrder !== null)>
                        <option value="">Select supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Location <span class="text-red-500">*</span></label>
                    <select wire:model.live="stock_location_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" @disabled($selectedOrder !== null)>
                        <option value="">Select location</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                    @error('stock_location_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                    <textarea wire:model.blur="notes" rows="3" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                    @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-8">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Receipt Items</h2>
                    <button type="button" wire:click="addItem" class="rounded-md border border-blue-300 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                        Add Item
                    </button>
                </div>
                <div class="space-y-4">
                    @foreach($items as $index => $item)
                        @php $product = $products->firstWhere('id', (int) ($item['product_id'] ?? 0)); @endphp
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Product <span class="text-red-500">*</span></label>
                                    <select wire:model.live="items.{{ $index }}.product_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                        <option value="">Select product</option>
                                        @foreach($products as $productOption)
                                            <option value="{{ $productOption->id }}">{{ $productOption->name }}</option>
                                        @endforeach
                                    </select>
                                    @error("items.$index.product_id") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Quantity <span class="text-red-500">*</span></label>
                                    <input type="number" step="0.01" min="0.01" wire:model.live="items.{{ $index }}.quantity" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    @error("items.$index.quantity") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Unit Cost <span class="text-red-500">*</span></label>
                                    <input type="number" step="0.01" min="0" wire:model.live="items.{{ $index }}.unit_cost" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    @error("items.$index.unit_cost") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                @if($product?->has_expiry)
                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Batch Number <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model.blur="items.{{ $index }}.batch_number" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                        @error("items.$index.batch_number") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Expiry Date <span class="text-red-500">*</span></label>
                                        <input type="date" wire:model.blur="items.{{ $index }}.expiry_date" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                        @error("items.$index.expiry_date") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                @endif
                                <div class="md:col-span-3">
                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Line Notes</label>
                                    <input type="text" wire:model.live.debounce.300ms="items.{{ $index }}.notes" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    @error("items.$index.notes") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div class="flex items-end justify-between md:justify-end">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ $currencyFormatter->formatValue(((float) ($item['quantity'] ?: 0)) * ((float) ($item['unit_cost'] ?: 0)), 2) }}
                                    </div>
                                    <button type="button" wire:click="removeItem({{ $index }})" class="ml-4 text-sm font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-6 flex items-center justify-between border-t border-gray-200 pt-4 dark:border-gray-700">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Total: <span class="font-semibold text-gray-900 dark:text-white">{{ $currencyFormatter->formatValue($total, 2) }}</span>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('purchase-receipts.index') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">Cancel</a>
                    <button type="submit" class="rounded-md bg-blue-600 px-6 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">Post Purchase Receipt</button>
                </div>
            </div>
        </form>
    </div>
</div>
