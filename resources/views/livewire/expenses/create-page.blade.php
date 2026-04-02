<div>
    <div class="mb-6">
        <a href="{{ route('expenses.index') }}" class="mb-2 inline-block text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
            <i class="fas fa-arrow-left mr-1"></i> Back to Expenses
        </a>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Record New Expense</h1>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-300">
            Please correct the highlighted errors and try again.
        </div>
    @endif

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        @include('livewire.expenses.partials.form-fields', [
            'submitAction' => 'save',
            'submitLabel' => 'Save Expense',
        ])
    </div>
</div>
