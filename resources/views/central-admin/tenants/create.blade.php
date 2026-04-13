@php
    $isTenantSupportRoute = request()->routeIs('support.*');
    $indexRoute = $isTenantSupportRoute ? 'support.tenants.index' : 'central.admin.tenants.index';
    $storeRoute = $isTenantSupportRoute ? 'support.tenants.store' : 'central.admin.tenants.store';
@endphp

<x-layouts.central-admin title="Create Tenant">
    <section class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-stone-500 dark:text-stone-400">Support Console</p>
                <h2 class="mt-2 text-xl font-semibold">Create tenant</h2>
                <p class="mt-2 text-sm text-stone-600 dark:text-stone-300">Set up a new tenant, assign its first domain, and create the first admin account in one flow.</p>
            </div>
            <a href="{{ route($indexRoute) }}" class="inline-flex items-center rounded-xl border border-stone-300 px-4 py-2 text-sm font-medium text-stone-700 transition hover:bg-stone-100 dark:border-stone-700 dark:text-stone-200 dark:hover:bg-stone-900">
                Back to tenants
            </a>
        </div>

        <form method="POST" action="{{ route($storeRoute) }}" class="space-y-6">
            @csrf

            <div class="rounded-2xl border border-stone-300/80 bg-white/85 p-6 shadow-sm dark:border-stone-800 dark:bg-stone-950/80">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-stone-500 dark:text-stone-400">Tenant Details</p>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <x-forms.input label="Tenant Name" name="name" :value="old('name')" required />
                    <x-forms.input label="Slug" name="slug" :value="old('slug')" placeholder="auto-generated if left blank" />
                    <x-forms.input label="Tenant Email" name="email" type="email" :value="old('email')" />
                    <x-forms.input label="Tenant Phone" name="phone" :value="old('phone')" />
                    <x-forms.input label="Primary Domain" name="primary_domain" :value="old('primary_domain')" placeholder="example.localhost" required />
                </div>
                <div class="mt-4">
                    <x-forms.checkbox label="Tenant is active" name="is_active" value="1" :checked="old('is_active', true)" />
                </div>
            </div>

            <div class="rounded-2xl border border-stone-300/80 bg-white/85 p-6 shadow-sm dark:border-stone-800 dark:bg-stone-950/80">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-stone-500 dark:text-stone-400">First Admin Account</p>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <x-forms.input label="Admin Name" name="admin_name" :value="old('admin_name')" required />
                    <x-forms.input label="Admin Email" name="admin_email" type="email" :value="old('admin_email')" required />
                    <x-forms.input label="Admin Phone" name="admin_phone" :value="old('admin_phone')" />
                    <div></div>
                    <x-forms.input label="Password" name="admin_password" type="password" required />
                    <x-forms.input label="Confirm Password" name="admin_password_confirmation" type="password" required />
                </div>
            </div>

            <div class="flex justify-end">
                <x-button type="primary">Create Tenant</x-button>
            </div>
        </form>
    </section>
</x-layouts.central-admin>
