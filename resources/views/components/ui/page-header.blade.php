@props([
    'title',
    'description' => null,
])

<div {{ $attributes->class('mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between') }}>
    <div class="min-w-0">
        @if (trim((string) $slot) !== '')
            <div class="mb-2">
                {{ $slot }}
            </div>
        @endif

        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $title }}</h1>

        @if ($description)
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
        @endif
    </div>

    @if (isset($actions))
        <div class="flex flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</div>
