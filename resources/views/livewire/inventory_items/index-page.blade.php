<div>
    <x-ui.page-header title="Inventory Items" description="Manage inventory items available for orders, invoices, purchasing, and stock tracking.">
        <x-slot:actions>
            @can('inventory-items.create')
                <x-ui.action-link href="{{ route('item-categories.index') }}" variant="secondary">
                    <i class="fas fa-tags mr-2"></i> Manage Item Categories
                </x-ui.action-link>
                <x-ui.action-link href="{{ route('inventory-items.create') }}" variant="primary">
                    <i class="fas fa-plus mr-2"></i> Add Inventory Item
                </x-ui.action-link>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-5">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search item name, SKU, barcode, or description"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
            <select
                wire:model.live="itemType"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
                <option value="">All item types</option>
                @foreach($itemTypes as $filterItemType)
                    <option value="{{ $filterItemType->value }}">{{ $filterItemType->label() }}</option>
                @endforeach
            </select>
            <select
                wire:model.live="status"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
                <option value="">All statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <select
                wire:model.live="category"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
                <option value="">All item categories</option>
                @foreach($categories as $filterCategory)
                    <option value="{{ $filterCategory->id }}">{{ $filterCategory->name }}</option>
                @endforeach
            </select>
            <x-ui.action-link
                tag="button"
                type="button"
                wire:click="clearFilters"
                variant="secondary"
            >
                Clear Filters
            </x-ui.action-link>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="w-1/4 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Inventory Item</th>
                        <th class="w-1/4 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Item Category</th>
                        <th class="w-1/4 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Description</th>
                        <th class="w-1/6 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Sale Price</th>
                        <th class="w-1/12 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                        <th class="w-1/6 px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($inventoryItems as $inventoryItem)
                        <tr wire:key="inventory-item-row-{{ $inventoryItem->id }}">
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <span class="block text-sm font-medium text-gray-900 dark:text-white">{{ $inventoryItem->name }}</span>
                                    <div class="flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400">
                                        <span>{{ $inventoryItem->item_type->label() }}</span>
                                        @if($inventoryItem->sku)
                                            <span>SKU: {{ $inventoryItem->sku }}</span>
                                        @endif
                                        @if($inventoryItem->barcode)
                                            <span>Barcode: {{ $inventoryItem->barcode }}</span>
                                        @endif
                                        @if($inventoryItem->tracks_inventory)
                                            <span>Stock: {{ number_format((float) $inventoryItem->quantity_on_hand, 2) }} {{ $inventoryItem->baseUnit?->abbreviation }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $inventoryItem->category->name ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="line-clamp-2 max-w-sm text-sm text-gray-500 dark:text-gray-400">{{ $inventoryItem->description ?? 'N/A' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                {{ $inventoryItem->sale_price === null ? 'N/A' : number_format((float) $inventoryItem->sale_price, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span @class([
                                    'rounded-full px-2 py-1 text-xs font-medium',
                                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' => $inventoryItem->is_active,
                                    'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400' => ! $inventoryItem->is_active,
                                ])>
                                    {{ $inventoryItem->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    @can('inventory-items.update')
                                        <x-ui.action-link href="{{ route('inventory-items.edit', $inventoryItem) }}" variant="warning">
                                            Edit
                                        </x-ui.action-link>
                                        <x-ui.action-link tag="button" type="button" wire:click="confirmDelete({{ $inventoryItem->id }})" variant="danger">
                                            Delete
                                        </x-ui.action-link>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No inventory items found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($inventoryItems->hasPages())
            <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
                {{ $inventoryItems->links() }}
            </div>
        @endif
    </div>

    @if($confirmingDeletion)
        <div class="relative z-50" aria-labelledby="inventory-item-delete-modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity dark:bg-gray-900/80"></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-lg border border-gray-200 bg-white text-left shadow-xl transition-all dark:border-gray-700 dark:bg-gray-800 sm:my-8 sm:w-full sm:max-w-lg">
                        <div class="bg-white px-4 pb-4 pt-5 dark:bg-gray-800 sm:p-6 sm:pb-4">
                            <div class="text-center sm:text-left">
                                <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white" id="inventory-item-delete-modal-title">
                                    Delete Confirmation
                                </h3>
                                <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    <p>Are you sure you want to delete <strong class="break-words">{{ $deletingInventoryItemName }}</strong>? This inventory item will be removed permanently.</p>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-900 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="button" wire:click="deleteInventoryItem" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-red-500 sm:ml-3 sm:w-auto">
                                Delete
                            </button>
                            <button type="button" wire:click="cancelDelete" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 transition-colors hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-100 dark:ring-gray-600 dark:hover:bg-gray-700 sm:mt-0 sm:w-auto">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

