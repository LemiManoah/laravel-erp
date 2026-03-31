<div>
    <div class="mb-6">
        <a href="{{ route('inventory.stock-locations.index') }}" class="mb-2 inline-block text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
            <i class="fas fa-arrow-left mr-1"></i> Back to Stock Locations
        </a>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Stock Location</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Update the stock location details.</p>
    </div>

    @if($errors->any())
        <div class="mb-6 rounded-md border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/30">
            <div class="flex">
                <div class="flex-shrink-0"><i class="fas fa-exclamation-circle text-red-400"></i></div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-300">There were {{ $errors->count() }} errors with your submission</h3>
                    <div class="mt-2 text-sm text-red-700 dark:text-red-400">
                        <ul class="list-disc space-y-1 pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="mb-6 max-w-2xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <form wire:submit="update">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" wire:model="name" placeholder="e.g., Main Warehouse" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="code" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Code</label>
                    <input type="text" id="code" wire:model="code" placeholder="e.g., WH-01" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="location_type" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                    <select id="location_type" wire:model="location_type" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Select type...</option>
                        @foreach($locationTypes as $locationType)
                            <option value="{{ $locationType->value }}">{{ $locationType->label() }}</option>
                        @endforeach
                    </select>
                    @error('location_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-6">
                    <div class="flex items-center">
                        <input type="checkbox" id="is_default" wire:model="is_default" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700">
                        <label for="is_default" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Set as Default</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="is_active" wire:model="is_active" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700">
                        <label for="is_active" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Active</label>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label for="address" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                    <textarea id="address" wire:model="address" rows="3" placeholder="Enter location address" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                    @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end border-t border-gray-200 pt-4 dark:border-gray-700">
                <a href="{{ route('inventory.stock-locations.index') }}" class="mr-3 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    Cancel
                </a>
                <button type="submit" wire:loading.attr="disabled" class="rounded-md bg-blue-600 px-6 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-70">
                    Update Location
                </button>
            </div>
        </form>
    </div>
</div>
