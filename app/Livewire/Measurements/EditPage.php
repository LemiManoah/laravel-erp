<?php

declare(strict_types=1);

namespace App\Livewire\Measurements;

use App\Actions\Measurement\UpdateMeasurementAction;
use App\Models\Measurement;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class EditPage extends Component
{
    #[Locked]
    public int $measurementId;

    public string $neck = '';
    public string $chest = '';
    public string $waist = '';
    public string $hips = '';
    public string $shoulder = '';
    public string $sleeve_length = '';
    public string $jacket_length = '';
    public string $trouser_waist = '';
    public string $trouser_length = '';
    public string $inseam = '';
    public string $thigh = '';
    public string $knee = '';
    public string $cuff = '';
    public string $height = '';
    public string $weight = '';
    public string $posture_notes = '';
    public string $fitting_notes = '';
    public string $measurement_date = '';
    public bool $is_current = false;

    public function mount(Measurement $measurement): void
    {
        abort_unless(auth()->user()?->can('update', $measurement), 403);

        $this->measurementId = $measurement->id;
        $this->neck = $this->stringValue($measurement->neck);
        $this->chest = $this->stringValue($measurement->chest);
        $this->waist = $this->stringValue($measurement->waist);
        $this->hips = $this->stringValue($measurement->hips);
        $this->shoulder = $this->stringValue($measurement->shoulder);
        $this->sleeve_length = $this->stringValue($measurement->sleeve_length);
        $this->jacket_length = $this->stringValue($measurement->jacket_length);
        $this->trouser_waist = $this->stringValue($measurement->trouser_waist);
        $this->trouser_length = $this->stringValue($measurement->trouser_length);
        $this->inseam = $this->stringValue($measurement->inseam);
        $this->thigh = $this->stringValue($measurement->thigh);
        $this->knee = $this->stringValue($measurement->knee);
        $this->cuff = $this->stringValue($measurement->cuff);
        $this->height = $this->stringValue($measurement->height);
        $this->weight = $this->stringValue($measurement->weight);
        $this->posture_notes = $measurement->posture_notes ?? '';
        $this->fitting_notes = $measurement->fitting_notes ?? '';
        $this->measurement_date = $measurement->measurement_date?->format('Y-m-d') ?? '';
        $this->is_current = $measurement->is_current;
    }

    protected function rules(): array
    {
        return [
            'neck' => ['nullable', 'numeric', 'min:0'],
            'chest' => ['nullable', 'numeric', 'min:0'],
            'waist' => ['nullable', 'numeric', 'min:0'],
            'hips' => ['nullable', 'numeric', 'min:0'],
            'shoulder' => ['nullable', 'numeric', 'min:0'],
            'sleeve_length' => ['nullable', 'numeric', 'min:0'],
            'jacket_length' => ['nullable', 'numeric', 'min:0'],
            'trouser_waist' => ['nullable', 'numeric', 'min:0'],
            'trouser_length' => ['nullable', 'numeric', 'min:0'],
            'inseam' => ['nullable', 'numeric', 'min:0'],
            'thigh' => ['nullable', 'numeric', 'min:0'],
            'knee' => ['nullable', 'numeric', 'min:0'],
            'cuff' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'posture_notes' => ['nullable', 'string'],
            'fitting_notes' => ['nullable', 'string'],
            'measurement_date' => ['required', 'date'],
            'is_current' => ['boolean'],
        ];
    }

    public function update(UpdateMeasurementAction $action): mixed
    {
        $measurement = Measurement::query()->findOrFail($this->measurementId);

        abort_unless(auth()->user()?->can('update', $measurement), 403);

        $this->validate();

        $action->handle($measurement, [
            'neck' => $this->nullableNumber($this->neck),
            'chest' => $this->nullableNumber($this->chest),
            'waist' => $this->nullableNumber($this->waist),
            'hips' => $this->nullableNumber($this->hips),
            'shoulder' => $this->nullableNumber($this->shoulder),
            'sleeve_length' => $this->nullableNumber($this->sleeve_length),
            'jacket_length' => $this->nullableNumber($this->jacket_length),
            'trouser_waist' => $this->nullableNumber($this->trouser_waist),
            'trouser_length' => $this->nullableNumber($this->trouser_length),
            'inseam' => $this->nullableNumber($this->inseam),
            'thigh' => $this->nullableNumber($this->thigh),
            'knee' => $this->nullableNumber($this->knee),
            'cuff' => $this->nullableNumber($this->cuff),
            'height' => $this->nullableNumber($this->height),
            'weight' => $this->nullableNumber($this->weight),
            'posture_notes' => $this->nullableText($this->posture_notes),
            'fitting_notes' => $this->nullableText($this->fitting_notes),
            'measurement_date' => $this->measurement_date,
            'is_current' => $this->is_current,
        ]);

        session()->flash('success', 'Measurements updated successfully.');

        return $this->redirectRoute('measurements.show', $measurement);
    }

    public function render(): View
    {
        return view('livewire.measurements.edit-page');
    }

    private function nullableNumber(string $value): float|int|null
    {
        return trim($value) === '' ? null : (float) $value;
    }

    private function nullableText(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function stringValue(mixed $value): string
    {
        return $value === null ? '' : (string) $value;
    }
}
