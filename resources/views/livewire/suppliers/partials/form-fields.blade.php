<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div>
        <label for="name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Supplier Name <span class="text-red-500">*</span></label>
        <input id="name" type="text" wire:model.blur="name" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="code" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Supplier Code</label>
        <input id="code" type="text" wire:model.blur="code" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="contact_person" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Contact Person</label>
        <input id="contact_person" type="text" wire:model.blur="contact_person" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        @error('contact_person') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="phone" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
        <input id="phone" type="text" wire:model.blur="phone" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="email" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
        <input id="email" type="email" wire:model.blur="email" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="tax_number" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Tax Number</label>
        <input id="tax_number" type="text" wire:model.blur="tax_number" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        @error('tax_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div class="md:col-span-2">
        <label for="address" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
        <textarea id="address" wire:model.blur="address" rows="3" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
        @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div class="md:col-span-2">
        <label for="notes" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
        <textarea id="notes" wire:model.blur="notes" rows="3" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
        @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            Active supplier
        </label>
    </div>
</div>
<div class="mt-6 flex justify-end border-t border-gray-200 pt-4 dark:border-gray-700">
    <a href="{{ route('suppliers.index') }}" class="mr-3 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">Cancel</a>
    <button type="submit" class="rounded-md bg-blue-600 px-6 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">{{ $submitLabel }}</button>
</div>
