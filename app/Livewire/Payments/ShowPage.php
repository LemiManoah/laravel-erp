<?php

declare(strict_types=1);

namespace App\Livewire\Payments;

use App\Actions\Payment\VoidPaymentAction;
use App\Models\Payment;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

final class ShowPage extends Component
{
    public Payment $payment;

    public string $void_reason = '';
    public bool $showVoidForm = false;

    public function mount(Payment $payment): void
    {
        abort_unless(auth()->user()?->can('view', $payment), 403);

        $this->payment = $payment->load([
            'invoice.customer',
            'receipt',
            'receiver',
            'voider',
            'currency',
            'paymentMethodDefinition',
        ]);
    }

    public function voidPayment(VoidPaymentAction $action): void
    {
        abort_unless(auth()->user()?->can('void', $this->payment), 403);

        $this->validate([
            'void_reason' => ['required', 'string', 'min:3'],
        ]);

        try {
            $action->handle($this->payment, $this->void_reason);
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }

            return;
        }

        $this->payment->refresh()->load([
            'invoice.customer',
            'receipt',
            'receiver',
            'voider',
            'currency',
            'paymentMethodDefinition',
        ]);
        $this->showVoidForm = false;
        $this->void_reason = '';
        session()->flash('success', 'Payment voided successfully.');
    }

    public function render(): View
    {
        return view('livewire.payments.show-page');
    }
}
