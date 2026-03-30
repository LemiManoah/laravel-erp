<?php

declare(strict_types=1);

namespace App\Actions\Currency;

use App\Models\Currency;
use Illuminate\Support\Facades\DB;

final readonly class UpdateCurrencyAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(Currency $currency, array $attributes): Currency
    {
        return DB::transaction(function () use ($currency, $attributes): Currency {
            if (($attributes['is_default'] ?? false) === true) {
                Currency::query()
                    ->whereKeyNot($currency->id)
                    ->update(['is_default' => false]);

                $attributes['is_active'] = true;
            }

            $currency->update([
                ...$attributes,
                'code' => strtoupper((string) $attributes['code']),
            ]);

            return $currency->fresh();
        });
    }
}
