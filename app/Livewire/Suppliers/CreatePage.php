<?php

declare(strict_types=1);

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class CreatePage extends Component
{
    public string $name = '';
    public string $code = '';
    public string $contact_person = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $tax_number = '';
    public string $notes = '';
    public bool $is_active = true;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('suppliers.create'), 403);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', tenant()->unique('suppliers', 'name')],
            'code' => ['nullable', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    public function save(): mixed
    {
        abort_unless(auth()->user()?->can('suppliers.create'), 403);

        $this->validate();

        if ($this->code !== '' && Supplier::query()->where('code', trim($this->code))->exists()) {
            $this->addError('code', 'That supplier code already exists.');

            return null;
        }

        Supplier::query()->create([
            'tenant_id' => tenant('id'),
            'name' => trim($this->name),
            'code' => $this->code === '' ? null : trim($this->code),
            'contact_person' => $this->contact_person === '' ? null : trim($this->contact_person),
            'email' => $this->email === '' ? null : trim($this->email),
            'phone' => $this->phone === '' ? null : trim($this->phone),
            'address' => $this->address === '' ? null : trim($this->address),
            'tax_number' => $this->tax_number === '' ? null : trim($this->tax_number),
            'notes' => $this->notes === '' ? null : trim($this->notes),
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Supplier created successfully.');

        return $this->redirectRoute('suppliers.index');
    }

    public function render(): View
    {
        return view('livewire.suppliers.create-page');
    }
}
