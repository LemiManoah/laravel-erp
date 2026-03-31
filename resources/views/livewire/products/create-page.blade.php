<div>
    <div class="mb-6">
        <a href="{{ route('products.index') }}" class="mb-2 inline-block text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
            <i class="fas fa-arrow-left mr-1"></i> Back to Products
        </a>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Create Product</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add a new product or item to your catalog.</p>
    </div>

    @if($errors->any())
        <div class="mb-6 rounded-md border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/30">
            <div class="flex">
                <div class="flex-shrink-0"><i class="fas fa-exclamation-circle text-red-400"></i></div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-300">There were {{ $errors->count() }} errors with your submission</h3>
                    <div class="mt-2 text-sm text-red-700 dark:text-red-400">
                        <ul class="list-disc space-y-1 pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="mb-6 max-w-4xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <form wire:submit="save">
            @include('livewire.products.partials.form-fields')

            <div class="flex justify-end border-t border-gray-200 pt-4 dark:border-gray-700">
                <a href="{{ route('products.index') }}" class="mr-3 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    Cancel
                </a>
                <button type="submit" wire:loading.attr="disabled" class="rounded-md bg-blue-600 px-6 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-70">
                    Create Product
                </button>
            </div>
        </form>
    </div>
</div>
