<div>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline dark:text-blue-400">Dashboard</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="mx-2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        <span class="text-gray-500 dark:text-gray-400">Password</span>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Update password</h1>
        <p class="mt-1 text-gray-600 dark:text-gray-400">Ensure your account is using a long, random password to stay secure</p>
    </div>

    <div class="p-6">
        <div class="flex flex-col gap-6 md:flex-row">
            @include('settings.partials.navigation')

            <div class="flex-1">
                <div class="mb-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="p-6">
                        @if (session('status') === 'password-updated')
                            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/50 dark:bg-green-900/20 dark:text-green-300">
                                Password updated successfully.
                            </div>
                        @endif

                        <div class="max-w-md">
                            <div class="mb-4">
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Current Password</label>
                                <input type="password" wire:model.blur="current_password" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                @error('current_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="mb-6">
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">New Password</label>
                                <input type="password" wire:model.blur="password" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="mb-6">
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                                <input type="password" wire:model.blur="password_confirmation" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            </div>
                            <button type="button" wire:click="save" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">Update Password</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
