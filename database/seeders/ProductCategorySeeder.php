<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

final class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->categories() as $category) {
            ProductCategory::query()->updateOrCreate(
                ['name' => $category['name']],
                $category,
            );
        }
    }

    /**
     * @return array<int, array{name: string, description: string, is_active: bool}>
     */
    private function categories(): array
    {
        return [
            ['name' => 'Grocery Staples', 'description' => 'Shelf-stable staple foods and household pantry items.', 'is_active' => true],
            ['name' => 'Fresh Produce', 'description' => 'Perishable fruits, vegetables, and farm-fresh produce.', 'is_active' => true],
            ['name' => 'Beverages', 'description' => 'Packaged drinks, water, and liquid refreshment products.', 'is_active' => true],
            ['name' => 'Farm Inputs', 'description' => 'Seeds, fertilizers, feeds, and other agricultural inputs.', 'is_active' => true],
            ['name' => 'Animal Feed & Vet', 'description' => 'Feeds, supplements, and veterinary consumables for livestock operations.', 'is_active' => true],
            ['name' => 'Household Goods', 'description' => 'Cleaning, daily-use, and home-care products.', 'is_active' => true],
            ['name' => 'Office Supplies', 'description' => 'Stationery and routine business consumables.', 'is_active' => true],
            ['name' => 'Hardware & Utilities', 'description' => 'Tools, fittings, and practical maintenance supplies.', 'is_active' => true],
            ['name' => 'Services', 'description' => 'Non-stock services billed through the ERP.', 'is_active' => true],
            ['name' => 'Health & Personal Care', 'description' => 'Personal hygiene products and other quick-moving care items.', 'is_active' => true],
        ];
    }
}
