<?php

declare(strict_types=1);

namespace App\Livewire\PaymentMethods;

use App\Actions\PaymentMethod\UpdatePaymentMethodAction;
use App\Models\PaymentMethod;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class EditPage extends Component
{
    #[Locked]
    public int $paymentMethodId;

    public string $name = '';
    public bool $is_active = true;
    public string $sort_order = '0';
    public string $notes = '';

    public function mount(PaymentMethod $paymentMethod): void
    {
        abort_unless(auth()->user()?->can('payment-methods.update'), 403);

        $this->paymentMethodId = $paymentMethod->id;
        $this->name = $paymentMethod->name;
        $this->is_active = $paymentMethod->is_active;
        $this->sort_order = (string) $paymentMethod->sort_order;
        $this->notes = $paymentMethod->notes ?? '';
    }

    protected function rules(): array
    {
        $paymentMethod = PaymentMethod::query()->find($this->paymentMethodId);

        return [
            'name' => ['required', 'string', 'max:255', tenant()->unique('payment_methods', 'name')->ignore($paymentMethod)],
            'is_active' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function update(UpdatePaymentMethodAction $action): mixed
    {
        abort_unless(auth()->user()?->can('payment-methods.update'), 403);

        $this->validate();

        $paymentMethod = PaymentMethod::query()->findOrFail($this->paymentMethodId);
        $action->handle($paymentMethod, [
            'name' => trim($this->name),
            'is_active' => $this->is_active,
            'sort_order' => (int) $this->sort_order,
            'notes' => $this->notes === '' ? null : trim($this->notes),
        ]);

        session()->flash('success', 'Payment method updated successfully.');

        return $this->redirectRoute('payment-methods.index');
    }

    public function render(): View
    {
        return view('livewire.payment-methods.edit-page');
    }
}
