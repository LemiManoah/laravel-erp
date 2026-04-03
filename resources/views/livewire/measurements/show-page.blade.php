<div>
    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/50 dark:bg-green-900/20 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <a href="{{ route('customers.measurements.index', $measurement->customer) }}" class="mb-2 inline-block text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
                <i class="fas fa-arrow-left mr-1"></i> Back to Measurement History
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Measurement Details</h1>
                <span @class([
                    'rounded-full px-2 py-1 text-xs font-medium',
                    'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' => $measurement->is_current,
                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' => ! $measurement->is_current,
                ])>
                    {{ $measurement->is_current ? 'Current' : 'Archived' }}
                </span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $measurement->customer->full_name }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('update', $measurement)
                <a href="{{ route('measurements.edit', $measurement) }}" class="rounded-md bg-amber-600 px-4 py-2 text-white transition hover:bg-amber-700">
                    <i class="fas fa-edit mr-2"></i> Edit
                </a>
            @endcan
            @can('delete', $measurement)
                <button type="button" wire:click="delete" wire:confirm="Delete this measurement record? This cannot be undone." class="rounded-md bg-red-600 px-4 py-2 text-white transition hover:bg-red-700">
                    <i class="fas fa-trash mr-2"></i> Delete
                </button>
            @endcan
        </div>
    </div>

    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Measurement Date</p>
                <p class="text-gray-900 dark:text-white">{{ $measurement->measurement_date?->format('M d, Y') ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Measured By</p>
                <p class="text-gray-900 dark:text-white">{{ $measurement->measurer?->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Customer</p>
                <p class="text-gray-900 dark:text-white">{{ $measurement->customer->full_name }}</p>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            @foreach([
                'Neck' => $measurement->neck,
                'Chest' => $measurement->chest,
                'Waist' => $measurement->waist,
                'Hips' => $measurement->hips,
                'Shoulder' => $measurement->shoulder,
                'Sleeve Length' => $measurement->sleeve_length,
                'Jacket Length' => $measurement->jacket_length,
                'Trouser Waist' => $measurement->trouser_waist,
                'Trouser Length' => $measurement->trouser_length,
                'Inseam' => $measurement->inseam,
                'Thigh' => $measurement->thigh,
                'Knee' => $measurement->knee,
                'Cuff' => $measurement->cuff,
                'Height' => $measurement->height,
                'Weight' => $measurement->weight,
            ] as $label => $value)
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $label }}</p>
                    <p class="text-gray-900 dark:text-white">{{ $value ?? '-' }}</p>
                </div>
            @endforeach
        </div>

        @if($measurement->posture_notes || $measurement->fitting_notes)
            <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Posture Notes</p>
                    <p class="mt-2 whitespace-pre-line text-sm text-gray-900 dark:text-white">{{ $measurement->posture_notes ?: 'N/A' }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Fitting Notes</p>
                    <p class="mt-2 whitespace-pre-line text-sm text-gray-900 dark:text-white">{{ $measurement->fitting_notes ?: 'N/A' }}</p>
                </div>
            </div>
        @endif
    </div>
</div>
