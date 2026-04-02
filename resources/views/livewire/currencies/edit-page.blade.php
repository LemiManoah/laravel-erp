<div>
    <div class="mb-6">
        <a href="{{ route('currencies.index') }}" class="mb-2 inline-block text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
            <i class="fas fa-arrow-left mr-1"></i> Back to Currencies
        </a>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Currency</h1>
    </div>
    <div class="max-w-4xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <form wire:submit="update">
            @include('livewire.currencies.partials.form-fields', ['submitLabel' => 'Update Currency'])
        </form>
    </div>
</div>
