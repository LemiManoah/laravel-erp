<div>
    <div class="mb-6">
        <a href="{{ route('measurements.show', $this->measurementId) }}" class="mb-2 inline-block text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
            <i class="fas fa-arrow-left mr-1"></i> Back to Measurement
        </a>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Measurement</h1>
    </div>

    <form wire:submit="update">
        @include('livewire.measurements.partials.form-fields', ['submitLabel' => 'Update Measurement'])
    </form>
</div>
