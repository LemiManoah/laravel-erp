<?php

declare(strict_types=1);

namespace App\Livewire\Expenses;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class IndexPage extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $category = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('viewAny', Expense::class), 403);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->category = '';
        $this->resetPage();
    }

    public function render(): View
    {
        $search = trim($this->search);

        $expenses = Expense::query()
            ->with('category')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($expenseQuery) use ($search): void {
                    $expenseQuery->where('description', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('vendor_name', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('reference_number', 'like', sprintf('%%%s%%', $search));
                });
            })
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->when($this->category !== '', fn ($query) => $query->where('expense_category_id', $this->category))
            ->latest('expense_date')
            ->paginate(10);

        $categories = ExpenseCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.expenses.index-page', [
            'expenses' => $expenses,
            'categories' => $categories,
        ]);
    }
}
