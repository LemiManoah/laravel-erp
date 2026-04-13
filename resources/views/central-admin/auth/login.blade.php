<x-layouts.auth :title="'Tenant Support Login'">
    <div class="overflow-hidden rounded-lg border border-stone-200 bg-white shadow-md dark:border-stone-800 dark:bg-stone-900">
        <div class="p-6">
            <div class="mb-5">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-amber-700 dark:text-amber-400">Milestone 2</p>
                <h1 class="mt-2 text-2xl font-bold text-stone-900 dark:text-stone-100">Tenant support access</h1>
                <p class="mt-2 text-sm text-stone-600 dark:text-stone-300">Only support users with tenant-management access can use this support console.</p>
            </div>

            <form method="POST" action="{{ route('central.login.store') }}" class="space-y-3">
                @csrf

                <div>
                    <x-forms.input label="Email" name="email" type="email" placeholder="support@localhost" autofocus />
                </div>

                <div>
                    <x-forms.input label="Password" name="password" type="password" placeholder="password" />
                    <div class="mt-2 flex justify-end">
                        <x-forms.checkbox label="Remember me" name="remember" />
                    </div>
                </div>

                <x-button type="primary" class="w-full">Sign In To Support Console</x-button>
            </form>
        </div>
    </div>
</x-layouts.auth>
