<?php

declare(strict_types=1);

namespace App\Livewire\Inventory\Units;

use App\Models\UnitOfMeasure;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class CreatePage extends Component
{
    public string $name = '';

    public string $abbreviation = '';

    public bool $is_active = true;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('units-of-measure.create'), 403);
    }

    protected function rules(): array
    {
        $tenant = tenant();

        return [
            'name' => ['required', 'string', 'max:255', $tenant->unique('units_of_measure', 'name')],
            'abbreviation' => ['required', 'string', 'max:10', $tenant->unique('units_of_measure', 'abbreviation')],
            'is_active' => ['boolean'],
        ];
    }

    public function save(): mixed
    {
        abort_unless(auth()->user()?->can('units-of-measure.create'), 403);

        $this->validate();

        UnitOfMeasure::create([
            'tenant_id' => tenant('id'),
            'name' => trim($this->name),
            'abbreviation' => trim($this->abbreviation),
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Unit of measure created successfully.');

        return $this->redirectRoute('inventory.units-of-measure.index');
    }

    public function render(): View
    {
        return view('livewire.inventory.units.create-page');
    }
}
