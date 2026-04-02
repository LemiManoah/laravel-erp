<?php

declare(strict_types=1);

namespace App\Livewire\Customers;

use App\Actions\Customer\CreateCustomerAction;
use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class CreatePage extends Component
{
    public string $full_name = '';
    public string $phone = '';
    public string $alternative_phone = '';
    public string $email = '';
    public string $address = '';
    public string $gender = '';
    public string $date_of_birth = '';
    public string $notes = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('customers.create'), 403);
    }

    protected function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', tenant()->unique('customers', 'phone')],
            'alternative_phone' => ['nullable', 'string'],
            'email' => ['nullable', 'email', tenant()->unique('customers', 'email')],
            'address' => ['nullable', 'string'],
            'gender' => ['nullable', 'string'],
            'date_of_birth' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function save(CreateCustomerAction $action): mixed
    {
        abort_unless(auth()->user()?->can('customers.create'), 403);

        $this->validate();

        $customer = $action->handle([
            'full_name' => trim($this->full_name),
            'phone' => trim($this->phone),
            'alternative_phone' => $this->alternative_phone === '' ? null : trim($this->alternative_phone),
            'email' => $this->email === '' ? null : trim($this->email),
            'address' => $this->address === '' ? null : trim($this->address),
            'gender' => $this->gender === '' ? null : $this->gender,
            'date_of_birth' => $this->date_of_birth === '' ? null : $this->date_of_birth,
            'notes' => $this->notes === '' ? null : trim($this->notes),
        ]);

        $customer->update([
            'customer_code' => 'CUST-'.str_pad((string) $customer->id, 5, '0', STR_PAD_LEFT),
        ]);

        session()->flash('success', 'Customer created successfully.');

        return $this->redirectRoute('customers.show', $customer);
    }

    public function render(): View
    {
        return view('livewire.customers.create-page');
    }
}
