<?php

declare(strict_types=1);

namespace App\Livewire\ExpenseCategories;

use App\Models\ExpenseCategory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class EditPage extends Component
{
    #[Locked]
    public int $categoryId;

    public string $name = '';
    public string $description = '';
    public bool $is_active = true;

    public function mount(ExpenseCategory $expenseCategory): void
    {
        abort_unless(auth()->user()?->can('expenses.update'), 403);

        $this->categoryId = $expenseCategory->id;
        $this->name = $expenseCategory->name;
        $this->description = $expenseCategory->description ?? '';
        $this->is_active = $expenseCategory->is_active;
    }

    protected function rules(): array
    {
        $category = ExpenseCategory::query()->find($this->categoryId);

        return [
            'name' => ['required', 'string', 'max:255', tenant()->unique('expense_categories', 'name')->ignore($category)],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    public function update(): mixed
    {
        abort_unless(auth()->user()?->can('expenses.update'), 403);

        $this->validate();

        $category = ExpenseCategory::query()->findOrFail($this->categoryId);
        $category->update([
            'name' => trim($this->name),
            'description' => $this->description === '' ? null : trim($this->description),
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Expense category updated successfully.');

        return $this->redirectRoute('expense-categories.index');
    }

    public function render(): View
    {
        return view('livewire.expense-categories.edit-page');
    }
}
