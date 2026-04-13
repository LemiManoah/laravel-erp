<!DOCTYPE html>
<html lang="en">

@php
    $isTenantSupportRoute = request()->routeIs('support.*');
    $dashboardRoute = $isTenantSupportRoute ? 'support.dashboard' : 'central.admin.dashboard';
    $tenantsRoute = $isTenantSupportRoute ? 'support.tenants.index' : 'central.admin.tenants.index';
    $logoutRoute = $isTenantSupportRoute ? 'logout' : 'central.logout';
@endphp

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Support Console' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-stone-100 text-stone-900 antialiased dark:bg-stone-950 dark:text-stone-100">
    <div class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(180,120,40,0.12),_transparent_45%),linear-gradient(180deg,#f7f3ec_0%,#f2ede4_45%,#ebe5db_100%)] dark:bg-[radial-gradient(circle_at_top,_rgba(180,120,40,0.18),_transparent_35%),linear-gradient(180deg,#1b1712_0%,#15120e_45%,#110f0b_100%)]">
        <header class="border-b border-stone-300/70 bg-white/80 backdrop-blur dark:border-stone-800 dark:bg-stone-950/80">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-amber-700 dark:text-amber-400">Support</p>
                    <h1 class="text-xl font-semibold">{{ $title ?? 'Support Console' }}</h1>
                </div>

                <div class="flex items-center gap-3 text-sm">
                    <span class="hidden text-stone-600 dark:text-stone-300 sm:inline">{{ auth()->user()?->name }}</span>
                    <form method="POST" action="{{ route($logoutRoute) }}">
                        @csrf
                        <button type="submit" class="rounded-md border border-stone-300 px-4 py-2 font-medium text-stone-700 transition hover:bg-stone-100 dark:border-stone-700 dark:text-stone-200 dark:hover:bg-stone-900">
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <div class="mx-auto grid max-w-7xl gap-6 px-6 py-8 lg:grid-cols-[240px_minmax(0,1fr)]">
            <aside class="rounded-2xl border border-stone-300/80 bg-white/85 p-4 shadow-sm backdrop-blur dark:border-stone-800 dark:bg-stone-950/80">
                <p class="px-3 pb-3 text-xs font-semibold uppercase tracking-[0.3em] text-stone-500 dark:text-stone-400">Milestone 2</p>
                <nav class="space-y-1">
                    <a href="{{ route($dashboardRoute) }}" @class([
                        'block rounded-xl px-3 py-2 text-sm font-medium transition',
                        'bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-100' => request()->routeIs('central.admin.dashboard') || request()->routeIs('support.dashboard'),
                        'text-stone-700 hover:bg-stone-100 dark:text-stone-200 dark:hover:bg-stone-900' => ! request()->routeIs('central.admin.dashboard') && ! request()->routeIs('support.dashboard'),
                    ])>
                        Overview
                    </a>
                    <a href="{{ route($tenantsRoute) }}" @class([
                        'block rounded-xl px-3 py-2 text-sm font-medium transition',
                        'bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-100' => request()->routeIs('central.admin.tenants.*') || request()->routeIs('support.tenants.*'),
                        'text-stone-700 hover:bg-stone-100 dark:text-stone-200 dark:hover:bg-stone-900' => ! request()->routeIs('central.admin.tenants.*') && ! request()->routeIs('support.tenants.*'),
                    ])>
                        Tenants
                    </a>
                </nav>
            </aside>

            <main>
                @if (session('status') || session('success') || session('error'))
                    <div @class([
                        'mb-6 rounded-2xl border px-4 py-3 text-sm',
                        'border-green-200 bg-green-50 text-green-800 dark:border-green-900 dark:bg-green-950/60 dark:text-green-200' => session('status') || session('success'),
                        'border-red-200 bg-red-50 text-red-800 dark:border-red-900 dark:bg-red-950/60 dark:text-red-200' => session('error'),
                    ])>
                        {{ session('status') ?? session('success') ?? session('error') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</body>

</html>
