<x-layouts.central-admin title="Tenant Support Overview">
    <section class="space-y-6">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-stone-300/80 bg-white/85 p-5 shadow-sm dark:border-stone-800 dark:bg-stone-950/80">
                <p class="text-sm text-stone-500 dark:text-stone-400">Total Tenants</p>
                <p class="mt-2 text-3xl font-semibold">{{ number_format($stats['total_tenants']) }}</p>
            </div>
            <div class="rounded-2xl border border-stone-300/80 bg-white/85 p-5 shadow-sm dark:border-stone-800 dark:bg-stone-950/80">
                <p class="text-sm text-stone-500 dark:text-stone-400">Active Tenants</p>
                <p class="mt-2 text-3xl font-semibold text-emerald-700 dark:text-emerald-300">{{ number_format($stats['active_tenants']) }}</p>
            </div>
            <div class="rounded-2xl border border-stone-300/80 bg-white/85 p-5 shadow-sm dark:border-stone-800 dark:bg-stone-950/80">
                <p class="text-sm text-stone-500 dark:text-stone-400">Inactive Tenants</p>
                <p class="mt-2 text-3xl font-semibold text-rose-700 dark:text-rose-300">{{ number_format($stats['inactive_tenants']) }}</p>
            </div>
            <div class="rounded-2xl border border-stone-300/80 bg-white/85 p-5 shadow-sm dark:border-stone-800 dark:bg-stone-950/80">
                <p class="text-sm text-stone-500 dark:text-stone-400">Tracked Domains</p>
                <p class="mt-2 text-3xl font-semibold">{{ number_format($stats['total_domains']) }}</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(280px,1fr)]">
            <div class="rounded-2xl border border-stone-300/80 bg-white/85 p-6 shadow-sm dark:border-stone-800 dark:bg-stone-950/80">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-stone-500 dark:text-stone-400">Recent Tenant Activity</p>
                        <h2 class="mt-2 text-xl font-semibold">Latest tenants and updates</h2>
                    </div>
                    <a href="{{ request()->routeIs('support.*') ? route('support.tenants.index') : route('central.admin.tenants.index') }}" class="text-sm font-medium text-amber-700 hover:text-amber-900 dark:text-amber-300 dark:hover:text-amber-100">View all tenants</a>
                </div>

                <div class="mt-6 overflow-hidden rounded-2xl border border-stone-200 dark:border-stone-800">
                    <table class="min-w-full divide-y divide-stone-200 text-sm dark:divide-stone-800">
                        <thead class="bg-stone-50 dark:bg-stone-900/70">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-stone-600 dark:text-stone-300">Tenant</th>
                                <th class="px-4 py-3 text-left font-semibold text-stone-600 dark:text-stone-300">Domains</th>
                                <th class="px-4 py-3 text-left font-semibold text-stone-600 dark:text-stone-300">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-stone-600 dark:text-stone-300">Updated</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-200 dark:divide-stone-800">
                            @forelse ($recent_tenants as $tenant)
                                <tr class="bg-white/70 dark:bg-stone-950/40">
                                    <td class="px-4 py-3">
                                        <div class="font-medium">{{ $tenant->name }}</div>
                                        <div class="text-xs text-stone-500 dark:text-stone-400">{{ $tenant->slug }}</div>
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
                                    <td class="px-4 py-3 text-stone-600 dark:text-stone-300">{{ optional($tenant->updated_at)->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-stone-500 dark:text-stone-400">No tenants found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-stone-300/80 bg-white/85 p-6 shadow-sm dark:border-stone-800 dark:bg-stone-950/80">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-stone-500 dark:text-stone-400">Current Slice</p>
                    <h2 class="mt-2 text-xl font-semibold">Milestone 2 started</h2>
                    <p class="mt-3 text-sm leading-6 text-stone-600 dark:text-stone-300">The first support slice is now in place: support-user access, a tenant overview dashboard, and a tenant directory for operational visibility.</p>
                    <div class="mt-4">
                        <a href="{{ request()->routeIs('support.*') ? route('support.tenants.create') : route('central.admin.tenants.create') }}" class="inline-flex items-center rounded-xl bg-amber-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-700 dark:bg-amber-500 dark:text-stone-950 dark:hover:bg-amber-400">
                            Create Tenant
                        </a>
                    </div>
                </div>

                <div class="rounded-2xl border border-stone-300/80 bg-white/85 p-6 shadow-sm dark:border-stone-800 dark:bg-stone-950/80">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-stone-500 dark:text-stone-400">Next Up</p>
                    <ul class="mt-3 space-y-3 text-sm text-stone-700 dark:text-stone-200">
                        <li>Create tenant onboarding and setup flow.</li>
                        <li>Add tenant settings editing from the platform side.</li>
                        <li>Add suspend and reactivate controls for tenants.</li>
                        <li>Add tenant maintenance operations and audit visibility.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</x-layouts.central-admin>
