<div class="space-y-6">
    <div>
        <label for="name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Role Name <span class="text-red-500">*</span></label>
        <input id="name" type="text" wire:model.blur="name" class="w-full max-w-sm rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="mb-3 block text-sm font-medium text-gray-700 dark:text-gray-300">Permissions</label>
        @error('selectedPermissions') <p class="mb-2 text-sm text-red-600">{{ $message }}</p> @enderror

        <div class="space-y-6">
            @foreach($permissions as $group => $groupPermissions)
                <div>
                    <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        {{ ucfirst(str_replace(['-', '_'], ' ', $group)) }}
                    </h3>
                    <div class="flex flex-wrap gap-4">
                        @foreach($groupPermissions as $permission)
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox"
                                    wire:model="selectedPermissions"
                                    value="{{ $permission->name }}"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                {{ ucfirst(str_replace(['-', '_'], ' ', explode('.', $permission->name)[1] ?? $permission->name)) }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
<div class="mt-6 flex justify-end gap-3 border-t border-gray-200 pt-4 dark:border-gray-700">
    <a href="{{ route('roles.index') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">Cancel</a>
    <button type="submit" class="rounded-md bg-blue-600 px-6 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">{{ $submitLabel }}</button>
</div>
