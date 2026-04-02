<div>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline dark:text-blue-400">Dashboard</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="mx-2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        <span class="text-gray-500 dark:text-gray-400">Appearance</span>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Appearance</h1>
        <p class="mt-1 text-gray-600 dark:text-gray-400">Update the appearance settings for your account</p>
    </div>

    <div class="p-6">
        <div class="flex flex-col gap-6 md:flex-row">
            @include('settings.partials.navigation')

            <div class="flex-1">
                <div class="mb-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="p-6">
                        <div class="mb-4">
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Theme</label>
                            <div class="inline-flex rounded-md shadow-sm" role="group">
                                <button type="button" wire:click="setTheme('light')" @class([
                                    'rounded-l-lg border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:border-gray-600 dark:hover:bg-gray-600 dark:hover:text-white',
                                    'bg-gray-100 text-blue-700 dark:bg-gray-600 dark:text-white' => $theme_preference === 'light',
                                    'bg-white text-gray-900 dark:bg-gray-700 dark:text-white' => $theme_preference !== 'light',
                                ])>
                                    Light
                                </button>
                                <button type="button" wire:click="setTheme('dark')" @class([
                                    'border-b border-t border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:border-gray-600 dark:hover:bg-gray-600 dark:hover:text-white',
                                    'bg-gray-100 text-blue-700 dark:bg-gray-600 dark:text-white' => $theme_preference === 'dark',
                                    'bg-white text-gray-900 dark:bg-gray-700 dark:text-white' => $theme_preference !== 'dark',
                                ])>
                                    Dark
                                </button>
                                <button type="button" wire:click="setTheme('system')" @class([
                                    'rounded-r-md border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:border-gray-600 dark:hover:bg-gray-600 dark:hover:text-white',
                                    'bg-gray-100 text-blue-700 dark:bg-gray-600 dark:text-white' => $theme_preference === 'system',
                                    'bg-white text-gray-900 dark:bg-gray-700 dark:text-white' => $theme_preference !== 'system',
                                ])>
                                    System
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
