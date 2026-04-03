<div>
    <x-ui.page-header title="Expenses" description="Review operating spend, categories, and voided transactions.">
        <x-slot:actions>
            @can('expenses.view')
                <x-ui.action-link href="{{ route('expense-categories.index') }}" variant="secondary">
                    <i class="fas fa-tags mr-2"></i> Manage Categories
                </x-ui.action-link>
            @endcan
            @can('create', \App\Models\Expense::class)
                <x-ui.action-link href="{{ route('expenses.create') }}" variant="primary">
                    <i class="fas fa-plus mr-2"></i> Record Expense
                </x-ui.action-link>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search description, vendor, or ref"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
            <select wire:model.live="status" class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">All statuses</option>
                <option value="valid">Valid</option>
                <option value="voided">Voided</option>
            </select>
            <select wire:model.live="category" class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">All categories</option>
                @foreach($categories as $filterCategory)
                    <option value="{{ $filterCategory->id }}">{{ $filterCategory->name }}</option>
                @endforeach
            </select>
            <x-ui.action-link tag="button" type="button" wire:click="clearFilters" variant="secondary">
                Clear Filters
            </x-ui.action-link>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    @forelse($expenses as $expense)
                        <tr wire:key="expense-row-{{ $expense->id }}">
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $expense->expense_date->format('M d, Y') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                {{ $expense->category->name }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ Str::limit($expense->description, 30) }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">
                                {{ $currencyFormatter->formatValue($expense->amount, 2, $expense->currency) }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span @class([
                                    'px-2 py-1 text-xs rounded-full font-medium',
                                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' => $expense->status === 'valid',
                                    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' => $expense->status === 'voided',
                                ])>
                                    {{ ucfirst($expense->status) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                <div class="flex flex-wrap gap-2">
                                    @can('view', $expense)
                                        <a href="{{ route('expenses.show', $expense) }}"
                                            class="inline-flex items-center rounded-md border border-slate-200 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                                            View
                                        </a>
                                    @endcan
                                    @can('update', $expense)
                                        <a href="{{ route('expenses.edit', $expense) }}"
                                            class="inline-flex items-center rounded-md border border-amber-200 px-2.5 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-50 dark:border-amber-800 dark:text-amber-300 dark:hover:bg-amber-900/30">
                                            Edit
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No expenses found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
            <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
</div>
