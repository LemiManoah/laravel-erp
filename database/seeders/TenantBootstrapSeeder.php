<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class TenantBootstrapSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CurrencySeeder::class,
            PaymentMethodSeeder::class,
            UnitOfMeasureSeeder::class,
            StockLocationSeeder::class,
            ExpenseCategorySeeder::class,
            ItemCategorySeeder::class,
        ]);
    }
}
