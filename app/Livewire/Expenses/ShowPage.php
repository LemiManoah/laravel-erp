<?php

declare(strict_types=1);

namespace App\Livewire\Expenses;

use App\Actions\Expense\VoidExpenseAction;
use App\Models\Expense;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class ShowPage extends Component
{
    public Expense $expense;
    public bool $showVoidForm = false;
    public string $void_reason = '';

    public function mount(Expense $expense): void
    {
        abort_unless(auth()->user()?->can('expenses.view'), 403);

        $this->expense = $expense->load(['category', 'creator', 'voider', 'currency']);
    }

    public function voidExpense(VoidExpenseAction $action): void
    {
        abort_unless(auth()->user()?->can('void', $this->expense), 403);

        $this->validate(['void_reason' => ['required', 'string', 'min:3']]);

        $action->handle($this->expense, $this->void_reason);

        $this->expense->refresh()->load(['category', 'creator', 'voider', 'currency']);
        $this->showVoidForm = false;
        $this->void_reason = '';
        session()->flash('success', 'Expense voided.');
    }

    public function render(): View
    {
        return view('livewire.expenses.show-page');
    }
}
