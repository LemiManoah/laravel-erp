<?php

declare(strict_types=1);

use App\Actions\Expense\VoidExpenseAction;
use App\Actions\Invoice\IssueInvoiceAction;
use App\Actions\Invoice\SyncInvoiceStatusesAction;
use App\Actions\Payment\CreatePaymentAction;
use App\Actions\Payment\VoidPaymentAction;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->currency = Currency::query()->create([
        'name' => 'Ugandan Shilling',
        'code' => 'UGX',
        'symbol' => 'USh',
        'decimal_places' => 0,
        'exchange_rate' => 1,
        'is_default' => true,
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->paymentMethod = PaymentMethod::query()->create([
        'name' => 'Cash',
        'slug' => 'cash',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->customer = Customer::query()->create([
        'customer_code' => 'CUST-FLOW-0001',
        'full_name' => 'Milestone Test Customer',
        'phone' => '+256700000001',
        'email' => 'milestone-customer@example.test',
        'created_by' => $this->user->id,
    ]);

    $this->expenseCategory = ExpenseCategory::query()->create([
        'name' => 'Operations',
        'description' => 'Operational expenses',
        'is_active' => true,
    ]);
});

it('issues a draft invoice once it has at least one item', function (): void {
    $invoice = createFinancialWorkflowInvoice($this->customer, $this->currency, $this->user, [
        'status' => 'draft',
        'issued_at' => null,
        'total_amount' => 120,
        'balance_due' => 120,
    ]);

    InvoiceItem::query()->create([
        'invoice_id' => $invoice->id,
        'item_name' => 'Custom Suit',
        'quantity' => 1,
        'unit_price' => 120,
        'line_total' => 120,
    ]);

    $issued = app(IssueInvoiceAction::class)->handle($invoice);

    expect($issued->status)->toBe('issued');
    expect($issued->issued_at)->not->toBeNull();
});

it('rejects issuing an invoice without any items', function (): void {
    $invoice = createFinancialWorkflowInvoice($this->customer, $this->currency, $this->user, [
        'status' => 'draft',
        'issued_at' => null,
    ]);

    try {
        app(IssueInvoiceAction::class)->handle($invoice);

        $this->fail('Expected issuing an empty invoice to throw a validation exception.');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey('invoice');
    }
});

it('caps overpayments to the outstanding balance, creates a receipt, and marks the invoice paid', function (): void {
    $invoice = createFinancialWorkflowInvoice($this->customer, $this->currency, $this->user, [
        'status' => 'issued',
        'issued_at' => now(),
        'total_amount' => 40,
        'amount_paid' => 0,
        'balance_due' => 40,
    ]);

    $payment = app(CreatePaymentAction::class)->handle([
        'currency_id' => $this->currency->id,
        'payment_date' => now()->toDateString(),
        'amount' => 50,
        'payment_method_id' => $this->paymentMethod->id,
        'reference_number' => 'PAY-FLOW-0001',
        'notes' => 'Customer cleared the balance.',
    ], $invoice);

    expect((float) $payment->amount)->toBe(40.0);
    expect($payment->receipt)->not->toBeNull();

    $invoice->refresh();

    expect($invoice->status)->toBe('paid');
    expect((float) $invoice->amount_paid)->toBe(40.0);
    expect((float) $invoice->balance_due)->toBe(0.0);
});

it('voids a payment and restores the invoice balance and status', function (): void {
    $invoice = createFinancialWorkflowInvoice($this->customer, $this->currency, $this->user, [
        'status' => 'paid',
        'issued_at' => now()->subDay(),
        'total_amount' => 100,
        'amount_paid' => 100,
        'balance_due' => 0,
        'due_date' => now()->addDay()->toDateString(),
    ]);

    $payment = Payment::query()->create([
        'invoice_id' => $invoice->id,
        'currency_id' => $this->currency->id,
        'payment_date' => now()->toDateString(),
        'amount' => 100,
        'payment_method_id' => $this->paymentMethod->id,
        'payment_method' => $this->paymentMethod->name,
        'reference_number' => 'PAY-FLOW-0002',
        'status' => 'valid',
        'received_by' => $this->user->id,
    ]);

    $voided = app(VoidPaymentAction::class)->handle($payment, 'Customer payment bounced.');

    expect($voided->status)->toBe('voided');
    expect($voided->void_reason)->toBe('Customer payment bounced.');

    $invoice->refresh();

    expect($invoice->status)->toBe('issued');
    expect((float) $invoice->amount_paid)->toBe(0.0);
    expect((float) $invoice->balance_due)->toBe(100.0);
});

it('syncs invoice overdue states in both directions', function (): void {
    $shouldBecomeOverdue = createFinancialWorkflowInvoice($this->customer, $this->currency, $this->user, [
        'invoice_number' => 'INV-FLOW-OVERDUE',
        'status' => 'issued',
        'issued_at' => now()->subDays(5),
        'due_date' => now()->subDay()->toDateString(),
        'total_amount' => 150,
        'amount_paid' => 0,
        'balance_due' => 150,
    ]);

    $shouldRevertToIssued = createFinancialWorkflowInvoice($this->customer, $this->currency, $this->user, [
        'invoice_number' => 'INV-FLOW-ISSUED',
        'status' => 'overdue',
        'issued_at' => now()->subDays(5),
        'due_date' => now()->addDay()->toDateString(),
        'total_amount' => 80,
        'amount_paid' => 0,
        'balance_due' => 80,
    ]);

    $shouldRevertToPartial = createFinancialWorkflowInvoice($this->customer, $this->currency, $this->user, [
        'invoice_number' => 'INV-FLOW-PARTIAL',
        'status' => 'overdue',
        'issued_at' => now()->subDays(5),
        'due_date' => now()->addDay()->toDateString(),
        'total_amount' => 200,
        'amount_paid' => 50,
        'balance_due' => 150,
    ]);

    app(SyncInvoiceStatusesAction::class)->handle();

    expect($shouldBecomeOverdue->fresh()->status)->toBe('overdue');
    expect($shouldRevertToIssued->fresh()->status)->toBe('issued');
    expect($shouldRevertToPartial->fresh()->status)->toBe('partially_paid');
});

it('voids an expense and prevents a second void attempt', function (): void {
    $expense = Expense::query()->create([
        'expense_category_id' => $this->expenseCategory->id,
        'currency_id' => $this->currency->id,
        'expense_date' => now()->toDateString(),
        'amount' => 25,
        'payment_method_id' => $this->paymentMethod->id,
        'payment_method' => $this->paymentMethod->name,
        'vendor_name' => 'Utility Vendor',
        'reference_number' => 'EXP-FLOW-0001',
        'description' => 'Electricity purchase',
        'status' => 'valid',
        'created_by' => $this->user->id,
    ]);

    $voided = app(VoidExpenseAction::class)->handle($expense, 'Duplicate expense.');

    expect($voided->status)->toBe('voided');
    expect($voided->void_reason)->toBe('Duplicate expense.');
    expect($voided->voided_by)->toBe($this->user->id);

    try {
        app(VoidExpenseAction::class)->handle($expense->fresh(), 'Second attempt');

        $this->fail('Expected voiding an already voided expense to throw a validation exception.');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey('expense');
    }
});

function createFinancialWorkflowInvoice(Customer $customer, Currency $currency, User $user, array $overrides = []): Invoice
{
    static $counter = 0;
    $counter++;

    return Invoice::query()->create(array_merge([
        'invoice_number' => sprintf('INV-FLOW-%04d', $counter),
        'customer_id' => $customer->id,
        'currency_id' => $currency->id,
        'invoice_date' => now()->toDateString(),
        'due_date' => now()->addDays(7)->toDateString(),
        'status' => 'issued',
        'subtotal_amount' => 100,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'total_amount' => 100,
        'amount_paid' => 0,
        'balance_due' => 100,
        'issued_at' => now(),
        'created_by' => $user->id,
    ], $overrides));
}
