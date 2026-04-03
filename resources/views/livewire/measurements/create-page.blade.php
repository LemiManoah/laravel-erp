<div>
    <div class="mb-6">
        <a href="{{ route('customers.measurements.index', $customer) }}" class="mb-2 inline-block text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
            <i class="fas fa-arrow-left mr-1"></i> Back to Measurement History
        </a>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Record New Measurements</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $customer->full_name }}</p>
    </div>

    <form wire:submit="save">
        @include('livewire.measurements.partials.form-fields', ['submitLabel' => 'Save Measurement Record'])
    </form>
</div>
