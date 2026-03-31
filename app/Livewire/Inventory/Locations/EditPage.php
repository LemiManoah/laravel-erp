<?php

declare(strict_types=1);

namespace App\Livewire\Inventory\Locations;

use App\Models\StockLocation;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class EditPage extends Component
{
    #[Locked]
    public int $locationId;

    public string $name = '';

    public string $code = '';

    public string $location_type = '';

    public string $address = '';

    public bool $is_default = false;

    public bool $is_active = true;

    public function mount(StockLocation $location): void
    {
        abort_unless(auth()->user()?->can('stock-locations.update'), 403);

        $this->locationId = $location->id;
        $this->name = $location->name;
        $this->code = $location->code ?? '';
        $this->location_type = $location->location_type ?? '';
        $this->address = $location->address ?? '';
        $this->is_default = $location->is_default;
        $this->is_active = $location->is_active;
    }

    protected function rules(): array
    {
        $tenant = tenant();
        $location = StockLocation::query()->find($this->locationId);

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                $tenant->unique('stock_locations', 'name')->ignore($location),
            ],
            'code' => [
                'nullable',
                'string',
                'max:20',
                $tenant->unique('stock_locations', 'code')->ignore($location),
            ],
            'location_type' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    public function update(): mixed
    {
        abort_unless(auth()->user()?->can('stock-locations.update'), 403);

        $this->validate();

        $location = StockLocation::query()->findOrFail($this->locationId);

        if ($this->is_default) {
            StockLocation::query()
                ->whereKeyNot($location->id)
                ->update(['is_default' => false]);
        }

        $location->update([
            'name' => trim($this->name),
            'code' => $this->code ? trim($this->code) : null,
            'location_type' => $this->location_type ? trim($this->location_type) : null,
            'address' => $this->address ? trim($this->address) : null,
            'is_default' => $this->is_default,
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Stock location updated successfully.');

        return $this->redirectRoute('inventory.stock-locations.index');
    }

    public function render(): View
    {
        return view('livewire.inventory.locations.edit-page');
    }
}
