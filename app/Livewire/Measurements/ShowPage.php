<?php

declare(strict_types=1);

namespace App\Livewire\Measurements;

use App\Actions\Measurement\DeleteMeasurementAction;
use App\Models\Measurement;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class ShowPage extends Component
{
    public Measurement $measurement;

    public function mount(Measurement $measurement): void
    {
        abort_unless(auth()->user()?->can('view', $measurement), 403);

        $this->measurement = $measurement->load(['customer', 'measurer']);
    }

    public function delete(DeleteMeasurementAction $action): mixed
    {
        abort_unless(auth()->user()?->can('delete', $this->measurement), 403);

        $customer = $this->measurement->customer;

        $action->handle($this->measurement);

        session()->flash('success', 'Measurement record deleted.');

        return $this->redirectRoute('customers.show', $customer);
    }

    public function render(): View
    {
        return view('livewire.measurements.show-page');
    }
}
