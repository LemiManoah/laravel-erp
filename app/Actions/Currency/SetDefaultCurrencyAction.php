<?php

declare(strict_types=1);

namespace App\Actions\Currency;

use App\Models\Currency;
use Illuminate\Support\Facades\DB;

final readonly class SetDefaultCurrencyAction
{
    public function handle(Currency $currency): Currency
    {
        return DB::transaction(function () use ($currency): Currency {
            Currency::query()->update(['is_default' => false]);

            $currency->update([
                'is_default' => true,
                'is_active' => true,
            ]);

            return $currency->fresh();
        });
    }
}
