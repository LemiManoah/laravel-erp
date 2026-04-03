<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ItemCategory;
use Illuminate\Database\Seeder;

final class ItemCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->categories() as $category) {
            ItemCategory::query()->updateOrCreate(
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
            ['name' => 'Grocery Staples', 'description' => 'Shelf-stable inventory items such as grains, flour, sugar, and packaged pantry stock.', 'is_active' => true],
            ['name' => 'Fresh Produce', 'description' => 'Perishable inventory items including vegetables, fruits, and other fresh produce.', 'is_active' => true],
            ['name' => 'Beverages', 'description' => 'Packaged drinks, juices, bottled water, and related sellable stock items.', 'is_active' => true],
            ['name' => 'Farm Inputs', 'description' => 'Agricultural inventory items such as fertilizers, seeds, and crop care supplies.', 'is_active' => true],
            ['name' => 'Animal Feed & Vet', 'description' => 'Feed, supplements, and veterinary inventory items for livestock operations.', 'is_active' => true],
            ['name' => 'Household Goods', 'description' => 'Cleaning and daily-use inventory items for household and retail use.', 'is_active' => true],
            ['name' => 'Office Supplies', 'description' => 'Stationery, printer stock, and other routine office inventory items.', 'is_active' => true],
            ['name' => 'Hardware & Utilities', 'description' => 'Tools, fittings, electricals, and general maintenance inventory items.', 'is_active' => true],
            ['name' => 'Services', 'description' => 'Non-stock service items billed through the ERP without inventory tracking.', 'is_active' => true],
            ['name' => 'Health & Personal Care', 'description' => 'Hygiene and personal-care inventory items with strong retail movement.', 'is_active' => true],
        ];
    }
}
