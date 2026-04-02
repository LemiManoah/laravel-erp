<?php

declare(strict_types=1);

namespace App\Livewire\Expenses;

use App\Actions\Expense\CreateExpenseAction;
use App\Models\Currency;
use App\Models\ExpenseCategory;
use App\Models\PaymentMethod;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class CreatePage extends Component
{
    public string $expense_category_id = '';
    public string $currency_id = '';
    public string $expense_date = '';
    public string $amount = '';
    public string $payment_method_id = '';
    public string $vendor_name = '';
    public string $reference_number = '';
    public string $description = '';
    public string $notes = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('expenses.create'), 403);

        $this->expense_date = now()->toDateString();

        $defaultCurrency = Currency::active()->where('is_default', true)->first()
            ?? Currency::active()->first();
        if ($defaultCurrency) {
            $this->currency_id = (string) $defaultCurrency->id;
        }
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

    public function save(CreateExpenseAction $action): mixed
    {
        abort_unless(auth()->user()?->can('expenses.create'), 403);

        $this->validate();

        $action->handle([
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

        session()->flash('success', 'Expense recorded successfully.');

        return $this->redirectRoute('expenses.index');
    }

    public function render(): View
    {
        return view('livewire.expenses.create-page', [
            'categories' => ExpenseCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'paymentMethods' => PaymentMethod::query()->active()->ordered()->get(),
            'currencies' => Currency::active()->ordered()->get(),
        ]);
    }
}
