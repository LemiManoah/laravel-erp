<?php

declare(strict_types=1);

namespace App\Livewire\ExpenseCategories;

use App\Models\ExpenseCategory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

final class IndexPage extends Component
{
    use WithPagination;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('expenses.view'), 403);
    }

    public function delete(int $categoryId): void
    {
        abort_unless(auth()->user()?->can('expenses.update'), 403);

        $category = ExpenseCategory::query()->withCount('expenses')->findOrFail($categoryId);

        if ($category->expenses_count > 0) {
            session()->flash('error', 'Cannot delete this category because it has expenses. Consider marking it as inactive instead.');

            return;
        }

        $category->delete();
        session()->flash('success', 'Expense category deleted successfully.');
    }

    public function render(): View
    {
        return view('livewire.expense-categories.index-page', [
            'categories' => ExpenseCategory::query()
                ->withCount('expenses')
                ->orderBy('name')
                ->paginate(15),
        ]);
    }
}
