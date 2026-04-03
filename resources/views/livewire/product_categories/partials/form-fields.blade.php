<div class="space-y-6">
    <div>
        <label for="name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Name <span class="text-red-500">*</span></label>
        <input id="name" type="text" wire:model.blur="name" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="description" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
        <textarea id="description" rows="4" wire:model.blur="description" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
        <input type="checkbox" wire:model.live="is_active" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
        Active
    </label>

    <div class="flex gap-3">
        <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">{{ $submitLabel }}</button>
        <a href="{{ route('product-categories.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">Cancel</a>
    </div>
</div>
