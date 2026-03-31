<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Inventory Batches</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Monitor batch balances, expiry dates, and stock location coverage.</p>
        </div>
        @can('inventory-movements.create')
            <a href="{{ route('inventory.movements.create') }}" class="inline-flex w-full items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 sm:w-auto">
                <i class="fas fa-plus mr-2"></i> Receive or Adjust Stock
            </a>
        @endcan
    </div>

    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-5">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search batch, product, or location" class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <select wire:model.live="status" class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">All statuses</option>
                @foreach($statuses as $batchStatus)
                    <option value="{{ $batchStatus->value }}">{{ $batchStatus->label() }}</option>
                @endforeach
            </select>
            <select wire:model.live="expiry" class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">All expiry states</option>
                <option value="near_expiry">Near Expiry</option>
                <option value="expired">Expired</option>
            </select>
            <select wire:model.live="location" class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">All locations</option>
                @foreach($locations as $locationOption)
                    <option value="{{ $locationOption->id }}">{{ $locationOption->name }}</option>
                @endforeach
            </select>
            <button type="button" wire:click="clearFilters" class="rounded-md bg-gray-900 px-4 py-2 text-sm text-white transition hover:bg-gray-700">Clear Filters</button>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Batch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Expiry</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">On Hand</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($batches as $batch)
                        <tr wire:key="batch-row-{{ $batch->id }}">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $batch->batch_number }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $batch->product->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $batch->location?->name ?? 'Unassigned' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $batch->expiry_date?->format('d M Y') ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ number_format((float) $batch->quantity_on_hand, 2) }}</td>
                            <td class="px-6 py-4">
                                <span @class([
                                    'rounded-full px-2 py-1 text-xs font-medium',
                                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' => $batch->status->value === 'active',
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' => $batch->status->value === 'depleted',
                                    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' => $batch->status->value === 'expired',
                                    'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300' => $batch->status->value === 'quarantined',
                                ])>{{ $batch->status->label() }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No inventory batches found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($batches->hasPages())
            <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
                {{ $batches->links() }}
            </div>
        @endif
    </div>
</div>
