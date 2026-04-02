<?php

declare(strict_types=1);

namespace App\Livewire\ExpenseCategories;

use App\Models\ExpenseCategory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class CreatePage extends Component
{
    public string $name = '';
    public string $description = '';
    public bool $is_active = true;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('expenses.create'), 403);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', tenant()->unique('expense_categories', 'name')],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    public function save(): mixed
    {
        abort_unless(auth()->user()?->can('expenses.create'), 403);

        $this->validate();

        ExpenseCategory::create([
            'name' => trim($this->name),
            'description' => $this->description === '' ? null : trim($this->description),
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Expense category created successfully.');

        return $this->redirectRoute('expense-categories.index');
    }

    public function render(): View
    {
        return view('livewire.expense-categories.create-page');
    }
}
