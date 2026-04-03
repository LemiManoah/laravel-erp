<div>
    <div class="mb-6">
        <a href="{{ route('item-categories.index') }}" class="mb-2 inline-block text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
            <i class="fas fa-arrow-left mr-1"></i> Back to Item Categories
        </a>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Item Category</h1>
    </div>

    <div class="max-w-3xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <form wire:submit="update">
            @include('livewire.item_categories.partials.form-fields', ['submitLabel' => 'Update Item Category'])
        </form>
    </div>
</div>

