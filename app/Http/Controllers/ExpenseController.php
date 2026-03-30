<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Expense\CreateExpenseAction;
use App\Actions\Expense\UpdateExpenseAction;
use App\Actions\Expense\VoidExpenseAction;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Http\Requests\VoidExpenseRequest;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

final readonly class ExpenseController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:expenses.view', only: ['index', 'show']),
            new Middleware('permission:expenses.create', only: ['create', 'store']),
            new Middleware('permission:expenses.update', only: ['edit', 'update']),
            new Middleware('permission:expenses.void', only: ['void']),
        ];
    }

    public function index(): View
    {
        $this->authorize('viewAny', Expense::class);

        return view('expenses.index');
    }

    public function create(): View
    {
        $this->authorize('create', Expense::class);

        $categories = ExpenseCategory::query()->where('is_active', true)->get();
        $paymentMethods = PaymentMethod::query()->active()->ordered()->get();
        $currencies = Currency::active()->ordered()->get();

        return view('expenses.create', compact('categories', 'paymentMethods', 'currencies'));
    }

    public function store(StoreExpenseRequest $request, CreateExpenseAction $action): RedirectResponse
    {
        $this->authorize('create', Expense::class);

        $action->handle($request->validated());

        return to_route('expenses.index')->with('success', 'Expense recorded successfully.');
    }

    public function show(Expense $expense): View
    {
        $this->authorize('view', $expense);

        $expense->load(['category', 'creator', 'voider', 'currency']);

        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense): View|RedirectResponse
    {
        $this->authorize('update', $expense);

        if ($expense->status === 'voided') {
            return to_route('expenses.show', $expense)->with('error', 'Voided expenses cannot be edited.');
        }

        $categories = ExpenseCategory::query()->where('is_active', true)->get();
        $paymentMethods = PaymentMethod::query()->ordered()->get();
        $currencies = Currency::active()->ordered()->get();

        return view('expenses.edit', compact('expense', 'categories', 'paymentMethods', 'currencies'));
    }

    public function update(UpdateExpenseRequest $request, Expense $expense, UpdateExpenseAction $action): RedirectResponse
    {
        $this->authorize('update', $expense);

        $action->handle($expense, $request->validated());

        return to_route('expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function void(VoidExpenseRequest $request, Expense $expense, VoidExpenseAction $action): RedirectResponse
    {
        $this->authorize('void', $expense);

        $action->handle($expense, $request->validated('void_reason'));

        return back()->with('success', 'Expense voided.');
    }
}
