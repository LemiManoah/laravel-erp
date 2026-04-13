@php
    $isTenantSupportRoute = request()->routeIs('support.*');
    $indexRoute = $isTenantSupportRoute ? 'support.tenants.index' : 'central.admin.tenants.index';
    $updateRoute = $isTenantSupportRoute ? 'support.tenants.update' : 'central.admin.tenants.update';
    $bootstrapRoute = $isTenantSupportRoute ? 'support.tenants.maintenance.bootstrap' : 'central.admin.tenants.maintenance.bootstrap';
    $demoRefreshRoute = $isTenantSupportRoute ? 'support.tenants.maintenance.demo-refresh' : 'central.admin.tenants.maintenance.demo-refresh';
@endphp

<x-layouts.central-admin title="Edit Tenant">
    <section class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-stone-500 dark:text-stone-400">Support Console</p>
                <h2 class="mt-2 text-xl font-semibold">Edit tenant</h2>
                <p class="mt-2 text-sm text-stone-600 dark:text-stone-300">Update the tenant profile, primary domain, and active status from one place.</p>
            </div>
            <a href="{{ route($indexRoute) }}" class="inline-flex items-center rounded-xl border border-stone-300 px-4 py-2 text-sm font-medium text-stone-700 transition hover:bg-stone-100 dark:border-stone-700 dark:text-stone-200 dark:hover:bg-stone-900">
                Back to tenants
            </a>
        </div>

        <form method="POST" action="{{ route($updateRoute, $tenant) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="rounded-2xl border border-stone-300/80 bg-white/85 p-6 shadow-sm dark:border-stone-800 dark:bg-stone-950/80">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-stone-500 dark:text-stone-400">Tenant Details</p>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <x-forms.input label="Tenant Name" name="name" :value="old('name', $tenant->name)" required />
                    <x-forms.input label="Slug" name="slug" :value="old('slug', $tenant->slug)" required />
                    <x-forms.input label="Tenant Email" name="email" type="email" :value="old('email', $tenant->email)" />
                    <x-forms.input label="Tenant Phone" name="phone" :value="old('phone', $tenant->phone)" />
                    <x-forms.input label="Primary Domain" name="primary_domain" :value="old('primary_domain', $primaryDomain?->domain)" required />
                </div>
                <div class="mt-4">
                    <x-forms.checkbox label="Tenant is active" name="is_active" value="1" :checked="old('is_active', $tenant->is_active)" />
                </div>
            </div>

            @if ($tenant->domains->count() > 1)
                <div class="rounded-2xl border border-stone-300/80 bg-white/85 p-6 shadow-sm dark:border-stone-800 dark:bg-stone-950/80">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-stone-500 dark:text-stone-400">Other Domains</p>
                    <div class="mt-4 space-y-2 text-sm text-stone-700 dark:text-stone-200">
                        @foreach ($tenant->domains->sortBy('id')->skip(1) as $domain)
                            <div class="rounded-xl border border-stone-200 px-4 py-3 dark:border-stone-800">{{ $domain->domain }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="rounded-2xl border border-stone-300/80 bg-white/85 p-6 shadow-sm dark:border-stone-800 dark:bg-stone-950/80">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-stone-500 dark:text-stone-400">Maintenance</p>
                <p class="mt-2 text-sm text-stone-600 dark:text-stone-300">Use these support tools to repair missing setup data or refresh the tenant's demo baseline.</p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <form method="POST" action="{{ route($bootstrapRoute, $tenant) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center rounded-lg border border-stone-300 px-4 py-2 text-sm font-medium text-stone-700 transition hover:bg-stone-100 dark:border-stone-700 dark:text-stone-200 dark:hover:bg-stone-900">
                            Re-run Core Setup
                        </button>
                    </form>
                    <form method="POST" action="{{ route($demoRefreshRoute, $tenant) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-700 dark:bg-amber-500 dark:text-stone-950 dark:hover:bg-amber-400">
                            Refresh Demo Baseline
                        </button>
                    </form>
                </div>
            </div>

            <div class="flex justify-end">
                <div class="flex flex-wrap gap-3">
                    @if ($tenant->is_active)
                        <form method="POST" action="{{ request()->routeIs('support.*') ? route('support.tenants.suspend', $tenant) : route('central.admin.tenants.suspend', $tenant) }}">
                            @csrf
                            <x-button type="danger">Suspend Tenant</x-button>
                        </form>
                    @else
                        <form method="POST" action="{{ request()->routeIs('support.*') ? route('support.tenants.reactivate', $tenant) : route('central.admin.tenants.reactivate', $tenant) }}">
                            @csrf
                            <button type="submit" class="text-white font-medium py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors flex items-center justify-center cursor-pointer bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500">Reactivate Tenant</button>
                        </form>
                    @endif
                    <x-button type="primary">Save Tenant Changes</x-button>
                </div>
            </div>
        </form>
    </section>
</x-layouts.central-admin>
