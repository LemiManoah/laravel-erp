<div>
    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/50 dark:bg-green-900/20 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-300">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('products.index') }}" class="mb-2 inline-block text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
                <i class="fas fa-arrow-left mr-1"></i> Back to Products
            </a>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Product Categories</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Manage categories used to classify products.</p>
        </div>
        <div>
            @can('products.create')
                <a href="{{ route('product-categories.create') }}" class="inline-flex w-full items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto">
                    <i class="fas fa-plus mr-2"></i> Add Category
                </a>
            @endcan
        </div>
    </div>

    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search category name or description"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
            <select wire:model.live="status" class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">All statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <button type="button" wire:click="clearFilters" class="rounded-md bg-gray-900 px-4 py-2 text-sm text-white transition hover:bg-gray-700">
                Clear Filters
            </button>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="w-1/4 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</th>
                        <th class="w-1/4 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Description</th>
                        <th class="w-1/4 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Products</th>
                        <th class="w-1/12 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                        <th class="w-1/6 px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($categories as $category)
                        <tr wire:key="product-category-row-{{ $category->id }}">
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $category->name }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="line-clamp-2 max-w-sm text-sm text-gray-500 dark:text-gray-400">{{ $category->description ?? 'N/A' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-900/30 dark:text-blue-300 dark:ring-blue-500/30">
                                    {{ $category->products_count }} Products
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span @class([
                                    'px-2 py-1 text-xs rounded-full font-medium',
                                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' => $category->is_active,
                                    'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400' => ! $category->is_active,
                                ])>
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    @can('products.update')
                                        <a href="{{ route('product-categories.edit', $category) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            Edit
                                        </a>
                                        <button type="button" wire:click="delete({{ $category->id }})" wire:confirm="Delete category '{{ addslashes($category->name) }}'? This cannot be undone." class="ml-2 text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            Delete
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No product categories found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
            <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
</div>
