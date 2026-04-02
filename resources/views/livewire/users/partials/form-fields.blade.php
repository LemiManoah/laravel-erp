<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div>
        <label for="name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name <span class="text-red-500">*</span></label>
        <input id="name" type="text" wire:model.blur="name" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="email" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Email Address <span class="text-red-500">*</span></label>
        <input id="email" type="email" wire:model.blur="email" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="phone" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
        <input id="phone" type="text" wire:model.blur="phone" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        {{-- spacer --}}
    </div>
    <div>
        <label for="password" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Password {{ isset($isCreate) && $isCreate ? '<span class="text-red-500">*</span>' : '(leave blank to keep current)' }}
        </label>
        <input id="password" type="password" wire:model.blur="password" autocomplete="new-password" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
        <input id="password_confirmation" type="password" wire:model.blur="password_confirmation" autocomplete="new-password" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
    </div>

    {{-- Roles --}}
    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Roles <span class="text-red-500">*</span></label>
        @error('selectedRoles') <p class="mb-2 text-sm text-red-600">{{ $message }}</p> @enderror
        <div class="flex flex-wrap gap-4">
            @foreach($roles as $role)
                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model="selectedRoles" value="{{ $role }}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    {{ $role }}
                </label>
            @endforeach
        </div>
    </div>

    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            Active user (can log in)
        </label>
        @error('is_active') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
<div class="mt-6 flex justify-end gap-3 border-t border-gray-200 pt-4 dark:border-gray-700">
    <a href="{{ route('users.index') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">Cancel</a>
    <button type="submit" class="rounded-md bg-blue-600 px-6 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">{{ $submitLabel }}</button>
</div>
