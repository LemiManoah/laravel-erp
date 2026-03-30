<?php

declare(strict_types=1);

namespace App\Actions\Currency;

use App\Models\Currency;
use Illuminate\Support\Facades\DB;

final readonly class CreateCurrencyAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(array $attributes): Currency
    {
        return DB::transaction(function () use ($attributes): Currency {
            if (($attributes['is_default'] ?? false) === true) {
                Currency::query()->update(['is_default' => false]);
                $attributes['is_active'] = true;
            }

            /** @var Currency $currency */
            $currency = Currency::query()->create([
                ...$attributes,
                'code' => strtoupper((string) $attributes['code']),
            ]);

            return $currency;
        });
    }
}
