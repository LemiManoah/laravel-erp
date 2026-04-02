<?php

declare(strict_types=1);

namespace App\Livewire\Expenses;

use App\Actions\Expense\UpdateExpenseAction;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\PaymentMethod;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class EditPage extends Component
{
    #[Locked]
    public int $expenseId;

    public string $expense_category_id = '';
    public string $currency_id = '';
    public string $expense_date = '';
    public string $amount = '';
    public string $payment_method_id = '';
    public string $vendor_name = '';
    public string $reference_number = '';
    public string $description = '';
    public string $notes = '';

    public function mount(Expense $expense): void
    {
        abort_unless(auth()->user()?->can('expenses.update'), 403);

        if ($expense->status === 'voided') {
            session()->flash('error', 'Voided expenses cannot be edited.');
            $this->redirectRoute('expenses.show', $expense);

            return;
        }

        $this->expenseId = $expense->id;
        $this->expense_category_id = (string) $expense->expense_category_id;
        $this->currency_id = (string) $expense->currency_id;
        $this->expense_date = $expense->expense_date->format('Y-m-d');
        $this->amount = (string) $expense->amount;
        $this->payment_method_id = $expense->payment_method_id ? (string) $expense->payment_method_id : '';
        $this->vendor_name = $expense->vendor_name ?? '';
        $this->reference_number = $expense->reference_number ?? '';
        $this->description = $expense->description;
        $this->notes = $expense->notes ?? '';
    }

    protected function rules(): array
    {
        $tenant = tenant();

        return [
            'expense_category_id' => ['required', 'integer', $tenant->exists('expense_categories', 'id')],
            'currency_id' => ['required', 'integer', $tenant->exists('currencies', 'id')],
            'expense_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method_id' => ['required', 'integer', $tenant->exists('payment_methods', 'id')],
            'vendor_name' => ['nullable', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function update(UpdateExpenseAction $action): mixed
    {
        abort_unless(auth()->user()?->can('expenses.update'), 403);

        $this->validate();

        $expense = Expense::query()->findOrFail($this->expenseId);

        $action->handle($expense, [
            'expense_category_id' => (int) $this->expense_category_id,
            'currency_id' => (int) $this->currency_id,
            'expense_date' => $this->expense_date,
            'amount' => (float) $this->amount,
            'payment_method_id' => (int) $this->payment_method_id,
            'vendor_name' => $this->vendor_name !== '' ? $this->vendor_name : null,
            'reference_number' => $this->reference_number !== '' ? $this->reference_number : null,
            'description' => $this->description,
            'notes' => $this->notes !== '' ? $this->notes : null,
        ]);

        session()->flash('success', 'Expense updated successfully.');

        return $this->redirectRoute('expenses.show', $expense);
    }

    public function render(): View
    {
        return view('livewire.expenses.edit-page', [
            'categories' => ExpenseCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'paymentMethods' => PaymentMethod::query()->ordered()->get(),
            'currencies' => Currency::active()->ordered()->get(),
        ]);
    }
}
