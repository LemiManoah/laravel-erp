<div>
    <div class="mb-6">
        <a href="{{ route('inventory.movements.index') }}" class="mb-2 inline-block text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
            <i class="fas fa-arrow-left mr-1"></i> Back to Inventory Movements
        </a>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Transfer Stock</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Move stock between locations while preserving batch traceability where required.</p>
    </div>

    <div class="mb-6 max-w-4xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <form wire:submit="save">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
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
                    <label for="quantity" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Quantity <span class="text-red-500">*</span></label>
                    <input id="quantity" type="number" step="0.01" min="0.01" wire:model="quantity" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error('quantity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="from_location_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">From Location <span class="text-red-500">*</span></label>
                    <select id="from_location_id" wire:model.live="from_location_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Select source location</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                    @error('from_location_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="to_location_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">To Location <span class="text-red-500">*</span></label>
                    <select id="to_location_id" wire:model="to_location_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Select destination location</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                    @error('to_location_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                @if($selectedProduct && ($selectedProduct->requires_batch_tracking || $selectedProduct->has_expiry))
                    <div class="md:col-span-2">
                        <label for="batch_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Batch <span class="text-red-500">*</span></label>
                        <select id="batch_id" wire:model="batch_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">Select batch</option>
                            @foreach($batches as $batch)
                                <option value="{{ $batch->id }}">{{ $batch->batch_number }} | Qty {{ number_format((float) $batch->quantity_on_hand, 2) }} | Exp {{ $batch->expiry_date?->format('d M Y') ?? 'N/A' }}</option>
                            @endforeach
                        </select>
                        @error('batch_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div>
                    <label for="movement_date" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Transfer Date <span class="text-red-500">*</span></label>
                    <input id="movement_date" type="datetime-local" wire:model="movement_date" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error('movement_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="notes" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                    <textarea id="notes" wire:model="notes" rows="3" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                    @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end border-t border-gray-200 pt-4 dark:border-gray-700">
                <a href="{{ route('inventory.movements.index') }}" class="mr-3 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">Cancel</a>
                <button type="submit" class="rounded-md bg-blue-600 px-6 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">Save Transfer</button>
            </div>
        </form>
    </div>
</div>
