<div class="grid grid-cols-1 gap-6 md:grid-cols-3">
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <h2 class="mb-4 border-b pb-2 text-lg font-medium text-gray-900 dark:text-white">Upper Body</h2>
        <div class="space-y-4">
            @foreach([
                'neck' => 'Neck',
                'chest' => 'Chest',
                'waist' => 'Waist',
                'shoulder' => 'Shoulder',
                'sleeve_length' => 'Sleeve Length',
                'jacket_length' => 'Jacket Length',
            ] as $field => $label)
                <div>
                    <label for="{{ $field }}" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
                    <input id="{{ $field }}" type="number" step="0.01" min="0" wire:model.blur="{{ $field }}" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error($field) <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <h2 class="mb-4 border-b pb-2 text-lg font-medium text-gray-900 dark:text-white">Lower Body</h2>
        <div class="space-y-4">
            @foreach([
                'trouser_waist' => 'Trouser Waist',
                'hips' => 'Hips',
                'trouser_length' => 'Trouser Length',
                'inseam' => 'Inseam',
                'thigh' => 'Thigh',
                'knee' => 'Knee',
                'cuff' => 'Cuff',
            ] as $field => $label)
                <div>
                    <label for="{{ $field }}" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
                    <input id="{{ $field }}" type="number" step="0.01" min="0" wire:model.blur="{{ $field }}" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error($field) <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            @endforeach
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="mb-4 border-b pb-2 text-lg font-medium text-gray-900 dark:text-white">General</h2>
            <div class="space-y-4">
                @foreach([
                    'height' => 'Height',
                    'weight' => 'Weight',
                ] as $field => $label)
                    <div>
                        <label for="{{ $field }}" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
                        <input id="{{ $field }}" type="number" step="0.01" min="0" wire:model.blur="{{ $field }}" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @error($field) <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endforeach
                <div>
                    <label for="measurement_date" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Measurement Date <span class="text-red-500">*</span></label>
                    <input id="measurement_date" type="date" wire:model.blur="measurement_date" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error('measurement_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model.live="is_current" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Set as Current Measurements
                </label>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="mb-4 border-b pb-2 text-lg font-medium text-gray-900 dark:text-white">Notes</h2>
            <div class="space-y-4">
                <div>
                    <label for="posture_notes" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Posture Notes</label>
                    <textarea id="posture_notes" rows="3" wire:model.blur="posture_notes" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                    @error('posture_notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="fitting_notes" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Fitting Notes</label>
                    <textarea id="fitting_notes" rows="3" wire:model.blur="fitting_notes" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                    @error('fitting_notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <button type="submit" class="w-full rounded-md bg-blue-600 px-4 py-3 font-semibold text-white transition hover:bg-blue-700">
            {{ $submitLabel }}
        </button>
    </div>
</div>
