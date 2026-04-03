<div>
    <x-ui.page-header title="Suppliers" description="Manage the vendors you buy stock and supplies from.">
        <x-slot:actions>
            @can('suppliers.create')
                <x-ui.action-link href="{{ route('suppliers.create') }}" variant="primary">
                    <i class="fas fa-plus mr-2"></i> New Supplier
                </x-ui.action-link>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Name, code, phone, email..." class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                <select wire:model.live="status" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">All suppliers</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="flex items-end">
                <x-ui.action-link tag="button" type="button" wire:click="clearFilters" variant="secondary">
                    Clear Filters
                </x-ui.action-link>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $supplier->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $supplier->code ?: 'No code' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                <div>{{ $supplier->contact_person ?: 'No contact person' }}</div>
                                <div>{{ $supplier->phone ?: $supplier->email ?: 'No contact details' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="{{ $supplier->is_active ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }}">
                                    {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm">
                                <div class="flex justify-end gap-3">
                                    @can('suppliers.update')
                                        <x-ui.action-link href="{{ route('suppliers.edit', $supplier) }}" variant="warning">
                                            Edit
                                        </x-ui.action-link>
                                    @endcan
                                    @can('suppliers.delete')
                                        <x-ui.action-link tag="button" type="button" wire:click="confirmDelete({{ $supplier->id }})" variant="danger">
                                            Delete
                                        </x-ui.action-link>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No suppliers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-700">
            {{ $suppliers->links() }}
        </div>
    </div>

    @if($confirmingDeletion)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Supplier</h2>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Delete <strong>{{ $deletingSupplierName }}</strong>? This action cannot be undone.</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" wire:click="cancelDelete" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-700 dark:border-gray-600 dark:text-gray-300">Cancel</button>
                    <button type="button" wire:click="deleteSupplier" class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white">Delete</button>
                </div>
            </div>
        </div>
    @endif
</div>
