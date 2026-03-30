<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Currency\CreateCurrencyAction;
use App\Actions\Currency\DeleteCurrencyAction;
use App\Actions\Currency\SetDefaultCurrencyAction;
use App\Actions\Currency\UpdateCurrencyAction;
use App\Http\Requests\DeleteCurrencyRequest;
use App\Http\Requests\SetDefaultCurrencyRequest;
use App\Http\Requests\StoreCurrencyRequest;
use App\Http\Requests\UpdateCurrencyRequest;
use App\Models\Currency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

final readonly class CurrencyController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:currencies.view', only: ['index']),
            new Middleware('permission:currencies.create', only: ['create', 'store']),
            new Middleware('permission:currencies.update', only: ['edit', 'update', 'setDefault']),
            new Middleware('permission:currencies.delete', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $this->authorize('viewAny', Currency::class);

        return view('currencies.index');
    }

    public function create(): View
    {
        $this->authorize('create', Currency::class);

        return view('currencies.create');
    }

    public function store(StoreCurrencyRequest $request, CreateCurrencyAction $action): RedirectResponse
    {
        $action->handle($request->validated());

        return to_route('currencies.index')->with('success', 'Currency created successfully.');
    }

    public function edit(Currency $currency): View
    {
        $this->authorize('update', $currency);

        return view('currencies.edit', compact('currency'));
    }

    public function update(UpdateCurrencyRequest $request, Currency $currency, UpdateCurrencyAction $action): RedirectResponse
    {
        $action->handle($currency, $request->validated());

        return to_route('currencies.index')->with('success', 'Currency updated successfully.');
    }

    public function setDefault(SetDefaultCurrencyRequest $request, Currency $currency, SetDefaultCurrencyAction $action): RedirectResponse
    {
        $action->handle($currency);

        return to_route('currencies.index')->with('success', 'Default currency updated successfully.');
    }

    public function destroy(DeleteCurrencyRequest $request, Currency $currency, DeleteCurrencyAction $action): RedirectResponse
    {
        $action->handle($currency);

        return to_route('currencies.index')->with('success', 'Currency deleted successfully.');
    }
}
