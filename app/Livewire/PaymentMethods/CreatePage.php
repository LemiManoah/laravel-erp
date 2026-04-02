<?php

declare(strict_types=1);

namespace App\Livewire\PaymentMethods;

use App\Actions\PaymentMethod\CreatePaymentMethodAction;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class CreatePage extends Component
{
    public string $name = '';
    public bool $is_active = true;
    public string $sort_order = '0';
    public string $notes = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('payment-methods.create'), 403);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', tenant()->unique('payment_methods', 'name')],
            'is_active' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function save(CreatePaymentMethodAction $action): mixed
    {
        abort_unless(auth()->user()?->can('payment-methods.create'), 403);

        $this->validate();

        $action->handle([
            'name' => trim($this->name),
            'is_active' => $this->is_active,
            'sort_order' => (int) $this->sort_order,
            'notes' => $this->notes === '' ? null : trim($this->notes),
        ]);

        session()->flash('success', 'Payment method created successfully.');

        return $this->redirectRoute('payment-methods.index');
    }

    public function render(): View
    {
        return view('livewire.payment-methods.create-page');
    }
}
