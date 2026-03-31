<?php

declare(strict_types=1);

namespace App\Livewire\Inventory\Units;

use App\Models\UnitOfMeasure;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class EditPage extends Component
{
    #[Locked]
    public int $unitId;

    public string $name = '';

    public string $abbreviation = '';

    public bool $is_active = true;

    public function mount(UnitOfMeasure $unit): void
    {
        abort_unless(auth()->user()?->can('units-of-measure.update'), 403);

        $this->unitId = $unit->id;
        $this->name = $unit->name;
        $this->abbreviation = $unit->abbreviation;
        $this->is_active = $unit->is_active;
    }

    protected function rules(): array
    {
        $tenant = tenant();
        $unit = UnitOfMeasure::query()->find($this->unitId);

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                $tenant->unique('units_of_measure', 'name')->ignore($unit),
            ],
            'abbreviation' => [
                'required',
                'string',
                'max:10',
                $tenant->unique('units_of_measure', 'abbreviation')->ignore($unit),
            ],
            'is_active' => ['boolean'],
        ];
    }

    public function update(): mixed
    {
        abort_unless(auth()->user()?->can('units-of-measure.update'), 403);

        $this->validate();

        $unit = UnitOfMeasure::query()->findOrFail($this->unitId);
        $unit->update([
            'name' => trim($this->name),
            'abbreviation' => trim($this->abbreviation),
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Unit of measure updated successfully.');

        return $this->redirectRoute('inventory.units-of-measure.index');
    }

    public function render(): View
    {
        return view('livewire.inventory.units.edit-page');
    }
}
