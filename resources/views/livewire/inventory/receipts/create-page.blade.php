<div>
    <div class="mb-6">
        <a href="{{ route('inventory.stocks.index') }}" class="mb-2 inline-block text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
            <i class="fas fa-arrow-left mr-1"></i> Back to Inventory Stocks
        </a>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Receive Stock</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Record opening stock, purchase receipts, returns, harvests, and other incoming stock events.</p>
    </div>

    <div class="mb-6 max-w-4xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <form wire:submit="save">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="receipt_type" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Receipt Type <span class="text-red-500">*</span></label>
                    <select id="receipt_type" wire:model.live="receipt_type" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @foreach($receiptTypes as $receiptType)
                            <option value="{{ $receiptType->value }}">{{ $receiptType->label() }}</option>
                        @endforeach
                    </select>
                    @error('receipt_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="movement_date" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Receipt Date <span class="text-red-500">*</span></label>
                    <input id="movement_date" type="datetime-local" wire:model="movement_date" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error('movement_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="product_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Product <span class="text-red-500">*</span></label>
                    <select id="product_id" wire:model.live="product_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Select product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                    @error('product_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="location_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Location <span class="text-red-500">*</span></label>
                    <select id="location_id" wire:model="location_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Select location</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                    @error('location_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="quantity" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Quantity <span class="text-red-500">*</span></label>
                    <input id="quantity" type="number" step="0.01" min="0.01" wire:model="quantity" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error('quantity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="unit_cost" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Unit Cost</label>
                    <input id="unit_cost" type="number" step="0.01" min="0" wire:model="unit_cost" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error('unit_cost') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                @if($selectedProduct?->has_expiry)
                    <div>
                        <label for="batch_number" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Batch Number <span class="text-red-500">*</span></label>
                        <input id="batch_number" type="text" wire:model="batch_number" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @error('batch_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="expiry_date" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Expiry Date <span class="text-red-500">*</span></label>
                        <input id="expiry_date" type="date" wire:model="expiry_date" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @error('expiry_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div class="md:col-span-2">
                    <label for="notes" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                    <textarea id="notes" wire:model="notes" rows="3" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                    @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end border-t border-gray-200 pt-4 dark:border-gray-700">
                <a href="{{ route('inventory.stocks.index') }}" class="mr-3 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">Cancel</a>
                <button type="submit" class="rounded-md bg-blue-600 px-6 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">Save Receipt</button>
            </div>
        </form>
    </div>
</div>
