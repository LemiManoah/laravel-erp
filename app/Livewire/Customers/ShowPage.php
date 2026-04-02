<?php

declare(strict_types=1);

namespace App\Livewire\Customers;

use App\Actions\Customer\DeleteCustomerAction;
use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

final class ShowPage extends Component
{
    public Customer $customer;

    public function mount(Customer $customer): void
    {
        abort_unless(auth()->user()?->can('customers.view'), 403);

        $this->customer = $customer->load([
            'measurements',
            'orders',
            'invoices.payments.receipt',
            'payments.invoice',
            'payments.receipt',
        ]);
    }

    public function delete(DeleteCustomerAction $action): mixed
    {
        abort_unless(auth()->user()?->can('customers.delete'), 403);

        try {
            $action->handle($this->customer);
        } catch (ValidationException $e) {
            session()->flash('error', $e->getMessage());

            return null;
        }

        session()->flash('success', 'Customer deleted successfully.');

        return $this->redirectRoute('customers.index');
    }

    public function render(): View
    {
        return view('livewire.customers.show-page');
    }
}
