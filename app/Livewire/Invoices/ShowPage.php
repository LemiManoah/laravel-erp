<?php

declare(strict_types=1);

namespace App\Livewire\Invoices;

use App\Actions\Invoice\CancelInvoiceAction;
use App\Actions\Invoice\IssueInvoiceAction;
use App\Actions\Invoice\SyncInvoiceStatusesAction;
use App\Actions\Payment\CreatePaymentAction;
use App\Actions\Payment\VoidPaymentAction;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class ShowPage extends Component
{
    public Invoice $invoice;

    // Payment form
    public string $payment_currency_id = '';
    public string $payment_amount = '';
    public string $payment_date = '';
    public string $payment_method_id = '';
    public string $payment_reference_number = '';
    public string $payment_notes = '';
    public bool $showPaymentForm = false;

    // Cancel form
    public string $cancellation_reason = '';
    public bool $showCancelForm = false;

    // Void payment
    public ?int $voidingPaymentId = null;
    public string $void_reason = '';

    public function mount(Invoice $invoice, SyncInvoiceStatusesAction $syncInvoiceStatuses): void
    {
        abort_unless(auth()->user()?->can('invoices.view'), 403);

        $syncInvoiceStatuses->handle();

        $this->invoice = $invoice->load(['customer', 'order', 'items', 'payments.receiver', 'payments.receipt', 'payments.voider', 'currency']);
        $this->payment_date = now()->toDateString();
        $this->payment_amount = (string) $this->invoice->balance_due;
        $this->payment_currency_id = (string) $this->invoice->currency_id;
    }

    public function issue(IssueInvoiceAction $action): void
    {
        abort_unless(auth()->user()?->can('issue', $this->invoice), 403);

        $action->handle($this->invoice);
        $this->invoice->refresh()->load(['customer', 'order', 'items', 'payments.receiver', 'payments.receipt', 'payments.voider', 'currency']);
        session()->flash('success', 'Invoice issued successfully.');
    }

    public function recordPayment(CreatePaymentAction $action): void
    {
        abort_unless(auth()->user()?->can('create', [Payment::class, $this->invoice]), 403);

        $this->validate([
            'payment_currency_id' => ['required', 'integer', tenant()->exists('currencies', 'id')],
            'payment_amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_method_id' => ['required', 'integer', tenant()->exists('payment_methods', 'id')],
            'payment_reference_number' => ['nullable', 'string', 'max:255'],
            'payment_notes' => ['nullable', 'string'],
        ]);

        try {
            $action->handle($this->invoice, [
                'currency_id' => (int) $this->payment_currency_id,
                'amount' => (float) $this->payment_amount,
                'payment_date' => $this->payment_date,
                'payment_method_id' => (int) $this->payment_method_id,
                'reference_number' => $this->payment_reference_number !== '' ? $this->payment_reference_number : null,
                'notes' => $this->payment_notes !== '' ? $this->payment_notes : null,
            ]);
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $this->addError('payment_'.$field, $messages[0]);
            }

            return;
        }

        $this->invoice->refresh()->load(['customer', 'order', 'items', 'payments.receiver', 'payments.receipt', 'payments.voider', 'currency']);
        $this->showPaymentForm = false;
        $this->payment_amount = (string) $this->invoice->balance_due;
        $this->reset(['payment_method_id', 'payment_reference_number', 'payment_notes']);
        session()->flash('success', 'Payment recorded successfully.');
    }

    public function voidPayment(VoidPaymentAction $action): void
    {
        if ($this->voidingPaymentId === null) {
            return;
        }

        $payment = Payment::query()->findOrFail($this->voidingPaymentId);
        abort_unless(auth()->user()?->can('void', $payment), 403);

        $this->validate(['void_reason' => ['required', 'string', 'min:3']]);

        $action->handle($payment, $this->void_reason);

        $this->invoice->refresh()->load(['customer', 'order', 'items', 'payments.receiver', 'payments.receipt', 'payments.voider', 'currency']);
        $this->voidingPaymentId = null;
        $this->void_reason = '';
        $this->payment_amount = (string) $this->invoice->balance_due;
        session()->flash('success', 'Payment voided successfully.');
    }

    public function cancelInvoice(CancelInvoiceAction $action): void
    {
        abort_unless(auth()->user()?->can('cancel', $this->invoice), 403);

        $this->validate(['cancellation_reason' => ['required', 'string', 'min:3']]);

        try {
            $action->handle($this->invoice, $this->cancellation_reason);
        } catch (ValidationException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        $this->invoice->refresh()->load(['customer', 'order', 'items', 'payments.receiver', 'payments.receipt', 'payments.voider', 'currency']);
        $this->showCancelForm = false;
        $this->cancellation_reason = '';
        session()->flash('success', 'Invoice cancelled successfully.');
    }

    public function render(): View
    {
        return view('livewire.invoices.show-page', [
            'paymentMethods' => PaymentMethod::query()->active()->ordered()->get(),
            'currencies' => Currency::active()->ordered()->get(),
        ]);
    }
}
