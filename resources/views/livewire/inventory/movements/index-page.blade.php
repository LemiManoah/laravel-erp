<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Inventory Movements</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">View the stock ledger across receipts, issues, returns, and adjustments.</p>
        </div>
        <div class="flex gap-2">
            @can('inventory-movements.create')
                <a href="{{ route('inventory.receipts.create') }}" class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i> Receive Stock
                </a>
                <a href="{{ route('inventory.adjustments.create') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    <i class="fas fa-sliders mr-2"></i> Adjust Stock
                </a>
            @endcan
            @can('inventory-transfers.create')
                <a href="{{ route('inventory.transfers.create') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    <i class="fas fa-right-left mr-2"></i> Transfer Stock
                </a>
            @endcan
        </div>
    </div>

    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-5">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search product, stock row, or location" class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <select wire:model.live="movementType" class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">All movement types</option>
                @foreach($movementTypes as $movementTypeOption)
                    <option value="{{ $movementTypeOption->value }}">{{ $movementTypeOption->label() }}</option>
                @endforeach
            </select>
            <select wire:model.live="direction" class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">All directions</option>
                @foreach($directions as $directionOption)
                    <option value="{{ $directionOption->value }}">{{ $directionOption->label() }}</option>
                @endforeach
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
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Stock Row</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Qty</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($movements as $movement)
                        <tr wire:key="movement-row-{{ $movement->id }}">
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $movement->movement_date->format('d M Y H:i') }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $movement->product->name }}</td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <span class="text-sm text-gray-900 dark:text-white">{{ $movement->movement_type->label() }}</span>
                                    <span @class([
                                        'inline-flex rounded-full px-2 py-1 text-xs font-medium',
                                        'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' => $movement->direction->value === 'in',
                                        'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' => $movement->direction->value === 'out',
                                    ])>{{ $movement->direction->label() }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $movement->location?->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $movement->inventoryStock?->batch_number ?? 'Standard stock row' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ number_format((float) $movement->quantity, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ number_format((float) $movement->balance_after, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No inventory movements found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
            <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>
