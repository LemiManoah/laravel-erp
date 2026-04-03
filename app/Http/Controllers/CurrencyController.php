<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Currency\SetDefaultCurrencyAction;
use App\Http\Requests\SetDefaultCurrencyRequest;
use App\Models\Currency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class CurrencyController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:currencies.update', only: ['setDefault']),
        ];
    }

    public function setDefault(SetDefaultCurrencyRequest $request, Currency $currency, SetDefaultCurrencyAction $action): RedirectResponse
    {
        $action->handle($currency);

        return to_route('currencies.index')->with('success', 'Default currency updated successfully.');
    }

}
