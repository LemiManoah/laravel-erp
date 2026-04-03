<div>
    <x-ui.page-header title="Expense Categories" description="Manage categories used to classify company expenses.">
        <a href="{{ route('expenses.index') }}" class="inline-block text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
            <i class="fas fa-arrow-left mr-1"></i> Back to Expenses
        </a>
        <x-slot:actions>
            @can('expenses.create')
                <x-ui.action-link href="{{ route('expense-categories.create') }}" variant="primary">
                    <i class="fas fa-plus mr-2"></i> Add Category
                </x-ui.action-link>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="w-1/4 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</th>
                        <th class="w-1/4 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Description</th>
                        <th class="w-1/4 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Expenses</th>
                        <th class="w-1/12 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                        <th class="w-1/6 px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($categories as $category)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $category->name }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="line-clamp-2 max-w-sm text-sm text-gray-500 dark:text-gray-400">{{ $category->description ?? 'N/A' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-900/30 dark:text-blue-300 dark:ring-blue-500/30">
                                    {{ $category->expenses_count }} Expenses
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span @class([
                                    'rounded-full px-2 py-1 text-xs font-medium',
                                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' => $category->is_active,
                                    'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400' => ! $category->is_active,
                                ])>
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    @can('expenses.update')
                                        <x-ui.action-link href="{{ route('expense-categories.edit', $category) }}" variant="warning">
                                            Edit
                                        </x-ui.action-link>
                                        <x-ui.action-link tag="button" type="button" wire:click="delete({{ $category->id }})" wire:confirm="Delete category '{{ $category->name }}'? This cannot be undone." variant="danger">
                                            Delete
                                        </x-ui.action-link>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No expense categories found.</td>
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
