<div>
    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/50 dark:bg-green-900/20 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('customers.show', $customer) }}" class="mb-2 inline-block text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
                <i class="fas fa-arrow-left mr-1"></i> Back to Customer
            </a>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Measurement History</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $customer->full_name }}</p>
        </div>
        @can('create', \App\Models\Measurement::class)
            <a href="{{ route('customers.measurements.create', $customer) }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm text-white transition hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i> Add Measurement
            </a>
        @endcan
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Chest</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Waist</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Measured By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    @forelse($measurements as $measurement)
                        <tr wire:key="measurement-row-{{ $measurement->id }}">
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $measurement->measurement_date?->format('M d, Y') ?? 'N/A' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $measurement->chest ?? '-' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $measurement->waist ?? '-' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $measurement->measurer?->name ?? 'N/A' }}</td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span @class([
                                    'rounded-full px-2 py-1 text-xs font-medium',
                                    'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' => $measurement->is_current,
                                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' => ! $measurement->is_current,
                                ])>
                                    {{ $measurement->is_current ? 'Current' : 'Archived' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    @can('view', $measurement)
                                        <a href="{{ route('measurements.show', $measurement) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">View</a>
                                    @endcan
                                    @can('update', $measurement)
                                        <a href="{{ route('measurements.edit', $measurement) }}" class="text-amber-600 hover:text-amber-900 dark:text-amber-400 dark:hover:text-amber-300">Edit</a>
                                    @endcan
                                    @can('delete', $measurement)
                                        <button type="button" wire:click="delete({{ $measurement->id }})" wire:confirm="Delete this measurement record? This cannot be undone." class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No measurements recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($measurements->hasPages())
            <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
                {{ $measurements->links() }}
            </div>
        @endif
    </div>
</div>
