<?php

declare(strict_types=1);

namespace App\Livewire\Measurements;

use App\Actions\Measurement\CreateMeasurementAction;
use App\Models\Customer;
use App\Models\Measurement;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class CreatePage extends Component
{
    public Customer $customer;

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
    public bool $is_current = true;

    public function mount(Customer $customer): void
    {
        abort_unless(auth()->user()?->can('create', Measurement::class), 403);

        $this->customer = $customer;
        $this->measurement_date = now()->toDateString();
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

    public function save(CreateMeasurementAction $action): mixed
    {
        abort_unless(auth()->user()?->can('create', Measurement::class), 403);

        $this->validate();

        $measurement = $action->handle($this->customer, $this->payload());

        session()->flash('success', 'Measurements recorded successfully.');

        return $this->redirectRoute('measurements.show', $measurement);
    }

    public function render(): View
    {
        return view('livewire.measurements.create-page');
    }

    private function payload(): array
    {
        return [
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
        ];
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
}
