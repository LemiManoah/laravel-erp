<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class TenantAppSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CurrencySeeder::class,
            PaymentMethodSeeder::class,
            UnitOfMeasureSeeder::class,
            StockLocationSeeder::class,
            SupplierSeeder::class,
            ExpenseCategorySeeder::class,
            ProductCategorySeeder::class,
            ProductSeeder::class,
            InventoryStockSeeder::class,
            PurchaseOrderSeeder::class,
            PurchaseReceiptSeeder::class,
            CustomerSeeder::class,
            MeasurementSeeder::class,
            OrderSeeder::class,
            InvoiceSeeder::class,
            PaymentSeeder::class,
            ReceiptSeeder::class,
            ExpenseSeeder::class,
        ]);
    }
}
