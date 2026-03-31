<?php

declare(strict_types=1);

namespace App\Livewire\Inventory\Locations;

use App\Models\StockLocation;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class CreatePage extends Component
{
    public string $name = '';

    public string $code = '';

    public string $location_type = '';

    public string $address = '';

    public bool $is_default = false;

    public bool $is_active = true;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('stock-locations.create'), 403);
    }

    protected function rules(): array
    {
        $tenant = tenant();

        return [
            'name' => ['required', 'string', 'max:255', $tenant->unique('stock_locations', 'name')],
            'code' => ['nullable', 'string', 'max:20', $tenant->unique('stock_locations', 'code')],
            'location_type' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    public function save(): mixed
    {
        abort_unless(auth()->user()?->can('stock-locations.create'), 403);

        $this->validate();

        if ($this->is_default) {
            StockLocation::query()->update(['is_default' => false]);
        }

        StockLocation::create([
            'tenant_id' => tenant('id'),
            'name' => trim($this->name),
            'code' => $this->code ? trim($this->code) : null,
            'location_type' => $this->location_type ? trim($this->location_type) : null,
            'address' => $this->address ? trim($this->address) : null,
            'is_default' => $this->is_default,
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Stock location created successfully.');

        return $this->redirectRoute('inventory.stock-locations.index');
    }

    public function render(): View
    {
        return view('livewire.inventory.locations.create-page');
    }
}
