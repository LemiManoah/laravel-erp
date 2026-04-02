<?php

declare(strict_types=1);

namespace App\Livewire\Customers;

use App\Actions\Customer\UpdateCustomerAction;
use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class EditPage extends Component
{
    #[Locked]
    public int $customerId;

    public string $full_name = '';
    public string $phone = '';
    public string $alternative_phone = '';
    public string $email = '';
    public string $address = '';
    public string $gender = '';
    public string $date_of_birth = '';
    public string $notes = '';

    public function mount(Customer $customer): void
    {
        abort_unless(auth()->user()?->can('customers.update'), 403);

        $this->customerId = $customer->id;
        $this->full_name = $customer->full_name;
        $this->phone = $customer->phone;
        $this->alternative_phone = $customer->alternative_phone ?? '';
        $this->email = $customer->email ?? '';
        $this->address = $customer->address ?? '';
        $this->gender = $customer->gender ?? '';
        $this->date_of_birth = $customer->date_of_birth?->format('Y-m-d') ?? '';
        $this->notes = $customer->notes ?? '';
    }

    protected function rules(): array
    {
        $customer = Customer::query()->find($this->customerId);

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', tenant()->unique('customers', 'phone')->ignore($customer)],
            'alternative_phone' => ['nullable', 'string'],
            'email' => ['nullable', 'email', tenant()->unique('customers', 'email')->ignore($customer)],
            'address' => ['nullable', 'string'],
            'gender' => ['nullable', 'string'],
            'date_of_birth' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function update(UpdateCustomerAction $action): mixed
    {
        abort_unless(auth()->user()?->can('customers.update'), 403);

        $this->validate();

        $customer = Customer::query()->findOrFail($this->customerId);
        $action->handle($customer, [
            'full_name' => trim($this->full_name),
            'phone' => trim($this->phone),
            'alternative_phone' => $this->alternative_phone === '' ? null : trim($this->alternative_phone),
            'email' => $this->email === '' ? null : trim($this->email),
            'address' => $this->address === '' ? null : trim($this->address),
            'gender' => $this->gender === '' ? null : $this->gender,
            'date_of_birth' => $this->date_of_birth === '' ? null : $this->date_of_birth,
            'notes' => $this->notes === '' ? null : trim($this->notes),
        ]);

        session()->flash('success', 'Customer updated successfully.');

        return $this->redirectRoute('customers.show', $customer);
    }

    public function render(): View
    {
        return view('livewire.customers.edit-page');
    }
}
