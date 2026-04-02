<div>
    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/50 dark:bg-green-900/20 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <a href="{{ route('expenses.index') }}" class="mb-2 inline-block text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
                <i class="fas fa-arrow-left mr-1"></i> Back to Expenses
            </a>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Expense Details</h1>
            <p class="text-gray-500 dark:text-gray-400">Category: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $expense->category->name }}</span></p>
        </div>
        @if($expense->status === 'valid')
            <div class="flex flex-wrap gap-2">
                @can('update', $expense)
                    <a href="{{ route('expenses.edit', $expense) }}" class="rounded-md bg-yellow-600 px-4 py-2 text-white transition hover:bg-yellow-700">
                        <i class="fas fa-edit mr-2"></i> Edit
                    </a>
                @endcan
                @can('void', $expense)
                    <button type="button" wire:click="$set('showVoidForm', true)" class="rounded-md bg-red-600 px-4 py-2 text-white transition hover:bg-red-700">
                        <i class="fas fa-ban mr-2"></i> Void
                    </button>
                @endcan
            </div>
        @endif
    </div>

    {{-- Inline Void Form --}}
    @if($showVoidForm)
        <div class="mb-6 rounded-lg border border-red-200 bg-white p-6 shadow-sm dark:border-red-700 dark:bg-gray-800">
            <h2 class="mb-2 text-lg font-medium text-gray-900 dark:text-white">Void Expense</h2>
            <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">This expense will no longer be counted in financial summaries.</p>
            <div class="mb-4">
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Reason for Voiding <span class="text-red-500">*</span></label>
                <textarea wire:model.blur="void_reason" rows="3" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                @error('void_reason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-3">
                <button type="button" wire:click="voidExpense" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">Confirm Void</button>
                <button type="button" wire:click="$set('showVoidForm', false)" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">Back</button>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-100 p-6 dark:border-gray-700">
                    <h2 class="text-lg font-bold uppercase tracking-wider text-gray-900 dark:text-white">Description</h2>
                    <p class="mt-2 text-lg leading-relaxed text-gray-700 dark:text-gray-300">{{ $expense->description }}</p>
                </div>
                <div class="p-6">
                    <h2 class="mb-4 text-lg font-bold uppercase tracking-wider text-gray-900 dark:text-white">Financial Info</h2>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Amount</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $currencyFormatter->formatValue($expense->amount, 2, $expense->currency) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Payment Method</p>
                            <p class="text-lg text-gray-900 dark:text-white">{{ $expense->payment_method }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Vendor</p>
                            <p class="text-lg text-gray-900 dark:text-white">{{ $expense->vendor_name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Reference #</p>
                            <p class="text-lg text-gray-900 dark:text-white">{{ $expense->reference_number ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($expense->notes)
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-900/30">
                    <h3 class="mb-2 text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Internal Notes:</h3>
                    <p class="whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">{{ $expense->notes }}</p>
                </div>
            @endif

            @if($expense->status === 'voided')
                <div class="rounded-lg border border-red-100 bg-red-50 p-6 dark:border-red-900/30 dark:bg-red-900/20">
                    <h3 class="mb-2 text-sm font-bold uppercase tracking-wider text-red-600 dark:text-red-400">Void Information:</h3>
                    <p class="text-sm text-red-700 dark:text-red-300">Voided on: {{ $expense->voided_at?->format('M d, Y H:i') ?? 'N/A' }}</p>
                    <p class="text-sm text-red-700 dark:text-red-300">Voided by: {{ $expense->voider->name ?? 'System' }}</p>
                    <p class="mt-2 text-sm italic text-red-700 dark:text-red-300">Reason: {{ $expense->void_reason ?? 'No reason recorded.' }}</p>
                </div>
            @endif
        </div>

        <div class="lg:col-span-1">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-bold uppercase tracking-wider text-gray-900 dark:text-white">Audit</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Expense Date</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $expense->expense_date->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</p>
                        <span @class([
                            'rounded-full px-2 py-1 text-xs font-medium',
                            'bg-green-100 text-green-800' => $expense->status === 'valid',
                            'bg-red-100 text-red-800' => $expense->status === 'voided',
                        ])>
                            {{ ucfirst($expense->status) }}
                        </span>
                    </div>
                    <div class="border-t border-gray-100 pt-4 dark:border-gray-700">
                        <p class="mb-1 text-[10px] font-bold uppercase text-gray-400">Recorded By</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ $expense->creator->name ?? 'System' }}</p>
                        <p class="mb-1 mt-2 text-[10px] font-bold uppercase text-gray-400">Created At</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ $expense->created_at?->format('M d, Y H:i') ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
