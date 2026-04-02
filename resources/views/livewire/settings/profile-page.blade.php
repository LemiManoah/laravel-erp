<div>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline dark:text-blue-400">Dashboard</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="mx-2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        <span class="text-gray-500 dark:text-gray-400">Profile</span>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Profile</h1>
        <p class="mt-1 text-gray-600 dark:text-gray-400">Update your name and email address</p>
    </div>

    <div class="p-6">
        <div class="flex flex-col gap-6 md:flex-row">
            @include('settings.partials.navigation')

            <div class="flex-1">
                <div class="mb-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="p-6">
                        @if (session('status') === 'Profile updated successfully')
                            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/50 dark:bg-green-900/20 dark:text-green-300">
                                Profile updated successfully.
                            </div>
                        @endif

                        <div class="mb-10 max-w-md">
                            <div class="mb-4">
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                <input type="text" wire:model.blur="name" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="mb-6">
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                <input type="email" wire:model.blur="email" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <button type="button" wire:click="save" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">Save</button>
                        </div>

                        <div class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-700">
                            <h2 class="mb-1 text-lg font-medium text-gray-800 dark:text-gray-200">Delete account</h2>
                            <p class="mb-4 text-gray-600 dark:text-gray-400">Delete your account and all of its resources</p>
                            <button type="button" wire:click="deleteAccount" wire:confirm="Are you sure you want to delete your account? This cannot be undone."
                                class="inline-flex items-center justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-red-700">
                                Delete account
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
