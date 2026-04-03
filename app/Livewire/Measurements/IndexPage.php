<?php

declare(strict_types=1);

namespace App\Livewire\Measurements;

use App\Actions\Measurement\DeleteMeasurementAction;
use App\Models\Customer;
use App\Models\Measurement;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

final class IndexPage extends Component
{
    use WithPagination;

    public Customer $customer;

    public function mount(Customer $customer): void
    {
        abort_unless(auth()->user()?->can('viewAny', Measurement::class), 403);

        $this->customer = $customer;
    }

    public function delete(int $measurementId, DeleteMeasurementAction $action): void
    {
        $measurement = Measurement::query()
            ->whereBelongsTo($this->customer)
            ->findOrFail($measurementId);

        abort_unless(auth()->user()?->can('delete', $measurement), 403);

        $action->handle($measurement);

        session()->flash('success', 'Measurement record deleted.');
    }

    public function render(): View
    {
        return view('livewire.measurements.index-page', [
            'measurements' => $this->customer->measurements()
                ->with('measurer')
                ->latest('measurement_date')
                ->latest('id')
                ->paginate(10),
        ]);
    }
}
