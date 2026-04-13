<x-layouts.central-admin title="Tenant Directory">
    <section class="rounded-2xl border border-stone-300/80 bg-white/85 p-6 shadow-sm dark:border-stone-800 dark:bg-stone-950/80">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-stone-500 dark:text-stone-400">Support Console</p>
                <h2 class="mt-2 text-xl font-semibold">Tenant directory</h2>
                <p class="mt-2 text-sm text-stone-600 dark:text-stone-300">This is the first operational list for Milestone 2. It gives support users a single place to review tenant status and registered domains.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-sm text-stone-500 dark:text-stone-400">{{ $tenants->count() }} tenant{{ $tenants->count() === 1 ? '' : 's' }}</div>
                <a href="{{ request()->routeIs('support.*') ? route('support.tenants.create') : route('central.admin.tenants.create') }}" class="inline-flex items-center rounded-xl bg-amber-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-700 dark:bg-amber-500 dark:text-stone-950 dark:hover:bg-amber-400">
                    Create Tenant
                </a>
            </div>
        </div>

        <div class="mt-6 overflow-hidden rounded-2xl border border-stone-200 dark:border-stone-800">
            <table class="min-w-full divide-y divide-stone-200 text-sm dark:divide-stone-800">
                <thead class="bg-stone-50 dark:bg-stone-900/70">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-stone-600 dark:text-stone-300">Tenant</th>
                        <th class="px-4 py-3 text-left font-semibold text-stone-600 dark:text-stone-300">Contact</th>
                        <th class="px-4 py-3 text-left font-semibold text-stone-600 dark:text-stone-300">Domains</th>
                        <th class="px-4 py-3 text-left font-semibold text-stone-600 dark:text-stone-300">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-stone-600 dark:text-stone-300">Created</th>
                        <th class="px-4 py-3 text-left font-semibold text-stone-600 dark:text-stone-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200 dark:divide-stone-800">
                    @forelse ($tenants as $tenant)
                        <tr class="bg-white/70 align-top dark:bg-stone-950/40">
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $tenant->name }}</div>
                                <div class="text-xs text-stone-500 dark:text-stone-400">{{ $tenant->slug }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div>{{ $tenant->email ?? 'No email' }}</div>
                                <div class="text-xs text-stone-500 dark:text-stone-400">{{ $tenant->phone ?? 'No phone' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @if ($tenant->domains->isEmpty())
                                    <span class="text-stone-500 dark:text-stone-400">No domains</span>
                                @else
                                    <div class="space-y-1">
                                        @foreach ($tenant->domains as $domain)
                                            <div>{{ $domain->domain }}</div>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                                    'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200' => $tenant->is_active,
                                    'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-200' => ! $tenant->is_active,
                                ])>
                                    {{ $tenant->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-stone-600 dark:text-stone-300">{{ optional($tenant->created_at)->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                <a href="{{ request()->routeIs('support.*') ? route('support.tenants.edit', $tenant) : route('central.admin.tenants.edit', $tenant) }}" class="inline-flex items-center rounded-lg border border-stone-300 px-3 py-1.5 text-sm font-medium text-stone-700 transition hover:bg-stone-100 dark:border-stone-700 dark:text-stone-200 dark:hover:bg-stone-900">
                                    Edit
                                </a>
                                <form method="POST" action="{{ $tenant->is_active ? (request()->routeIs('support.*') ? route('support.tenants.suspend', $tenant) : route('central.admin.tenants.suspend', $tenant)) : (request()->routeIs('support.*') ? route('support.tenants.reactivate', $tenant) : route('central.admin.tenants.reactivate', $tenant)) }}">
                                    @csrf
                                    <button type="submit" @class([
                                        'inline-flex items-center rounded-lg px-3 py-1.5 text-sm font-medium transition',
                                        'bg-rose-600 text-white hover:bg-rose-700 dark:bg-rose-500 dark:text-stone-950 dark:hover:bg-rose-400' => $tenant->is_active,
                                        'bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-500 dark:text-stone-950 dark:hover:bg-emerald-400' => ! $tenant->is_active,
                                    ])>
                                        {{ $tenant->is_active ? 'Suspend' : 'Reactivate' }}
                                    </button>
                                </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-stone-500 dark:text-stone-400">No tenants found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.central-admin>
