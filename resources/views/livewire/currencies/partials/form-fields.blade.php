<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div>
        <label for="name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Currency Name <span class="text-red-500">*</span></label>
        <input id="name" type="text" wire:model.blur="name" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="code" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Code (3 letters) <span class="text-red-500">*</span></label>
        <input id="code" type="text" wire:model.blur="code" maxlength="3" placeholder="USD" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm uppercase dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="symbol" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Symbol <span class="text-red-500">*</span></label>
        <input id="symbol" type="text" wire:model.blur="symbol" placeholder="$" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        @error('symbol') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="decimal_places" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Decimal Places <span class="text-red-500">*</span></label>
        <input id="decimal_places" type="number" wire:model.blur="decimal_places" min="0" max="4" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        @error('decimal_places') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="exchange_rate" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Exchange Rate <span class="text-red-500">*</span></label>
        <input id="exchange_rate" type="number" wire:model.blur="exchange_rate" step="0.0001" min="0" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        @error('exchange_rate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="sort_order" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Sort Order <span class="text-red-500">*</span></label>
        <input id="sort_order" type="number" wire:model.blur="sort_order" min="0" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        @error('sort_order') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div class="md:col-span-2 flex gap-6">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            Active currency
        </label>
        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <input type="checkbox" wire:model="is_default" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            Default currency
        </label>
    </div>
</div>
<div class="mt-6 flex justify-end gap-3 border-t border-gray-200 pt-4 dark:border-gray-700">
    <a href="{{ route('currencies.index') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">Cancel</a>
    <button type="submit" class="rounded-md bg-blue-600 px-6 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">{{ $submitLabel }}</button>
</div>
