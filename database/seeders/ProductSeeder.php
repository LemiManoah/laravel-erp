<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ProductItemType;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Seeder;

final class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ProductCategory::query()->get()->keyBy('name');
        $units = UnitOfMeasure::query()->get()->keyBy('abbreviation');

        foreach ($this->products() as $itemData) {
            $category = $itemData['category'];
            $unit = $itemData['base_unit'] ?? null;
            $parentItemName = $itemData['parent_item'] ?? null;
            $salePrice = $itemData['sale_price'] ?? null;
            $purchasePrice = $itemData['purchase_price'] ?? null;

            unset(
                $itemData['category'],
                $itemData['base_unit'],
                $itemData['parent_item'],
                $itemData['sale_price'],
                $itemData['purchase_price'],
            );

            $product = Product::query()->updateOrCreate(
                ['name' => $itemData['name']],
                [
                    ...$itemData,
                    'product_category_id' => $categories->get($category)?->id,
                    'base_unit_id' => $unit === null ? null : $units->get($unit)?->id,
                    'parent_item_id' => $parentItemName === null
                        ? null
                        : Product::query()->where('name', $parentItemName)->value('id'),
                ],
            );

            $product->defaultPrice()->updateOrCreate(
                ['tenant_id' => tenant('id'), 'product_id' => $product->id],
                [
                    'sale_price' => $salePrice,
                    'purchase_price' => $purchasePrice,
                ],
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function products(): array
    {
        return [
            [
                'category' => 'Grocery Staples',
                'name' => 'Rice Pack',
                'description' => 'Parent inventory item for retail rice pack variants.',
                'item_type' => ProductItemType::StockItem,
                'tracks_inventory' => true,
                'is_sellable' => false,
                'is_purchasable' => false,
                'base_unit' => 'pc',
                'purchase_price' => null,
                'sale_price' => null,
                'reorder_level' => 20,
                'has_variants' => true,
                'parent_item' => null,
                'has_expiry' => false,
                'is_serialized' => false,
                'is_active' => true,
            ],
            [
                'category' => 'Grocery Staples',
                'name' => 'Rice 1kg Pack',
                'description' => 'Pre-packed polished rice for retail sale.',
                'item_type' => ProductItemType::StockItem,
                'tracks_inventory' => true,
                'is_sellable' => true,
                'is_purchasable' => true,
                'base_unit' => 'pc',
                'purchase_price' => 2500.00,
                'sale_price' => 3200.00,
                'reorder_level' => 20,
                'has_variants' => false,
                'parent_item' => 'Rice Pack',
                'has_expiry' => false,
                'is_serialized' => false,
                'is_active' => true,
            ],
            [
                'category' => 'Fresh Produce',
                'name' => 'Tomatoes Crate',
                'description' => 'Fresh tomatoes sold by crate for market and shop restocking.',
                'item_type' => ProductItemType::FinishedGood,
                'tracks_inventory' => true,
                'is_sellable' => true,
                'is_purchasable' => true,
                'base_unit' => 'box',
                'purchase_price' => 28000.00,
                'sale_price' => 35000.00,
                'reorder_level' => 5,
                'has_variants' => false,
                'parent_item' => null,
                'has_expiry' => true,
                'is_serialized' => false,
                'is_active' => true,
            ],
            [
                'category' => 'Beverages',
                'name' => 'Orange Juice',
                'description' => 'Parent inventory item for juice size variants.',
                'item_type' => ProductItemType::StockItem,
                'tracks_inventory' => true,
                'is_sellable' => false,
                'is_purchasable' => false,
                'base_unit' => 'pc',
                'purchase_price' => null,
                'sale_price' => null,
                'reorder_level' => 12,
                'has_variants' => true,
                'parent_item' => null,
                'has_expiry' => true,
                'is_serialized' => false,
                'is_active' => true,
            ],
            [
                'category' => 'Beverages',
                'name' => 'Orange Juice 1L',
                'description' => 'Packaged orange juice in one-litre cartons.',
                'item_type' => ProductItemType::StockItem,
                'tracks_inventory' => true,
                'is_sellable' => true,
                'is_purchasable' => true,
                'base_unit' => 'pc',
                'purchase_price' => 4200.00,
                'sale_price' => 5500.00,
                'reorder_level' => 12,
                'has_variants' => false,
                'parent_item' => 'Orange Juice',
                'has_expiry' => true,
                'is_serialized' => false,
                'is_active' => true,
            ],
            [
                'category' => 'Farm Inputs',
                'name' => 'NPK Fertilizer 50kg',
                'description' => 'General-purpose fertilizer for farm and agro-input stores.',
                'item_type' => ProductItemType::RawMaterial,
                'tracks_inventory' => true,
                'is_sellable' => true,
                'is_purchasable' => true,
                'base_unit' => 'bag',
                'purchase_price' => 98000.00,
                'sale_price' => 115000.00,
                'reorder_level' => 10,
                'has_variants' => false,
                'parent_item' => null,
                'has_expiry' => false,
                'is_serialized' => false,
                'is_active' => true,
            ],
            [
                'category' => 'Household Goods',
                'name' => 'Laundry Soap Bar',
                'description' => 'Everyday household cleaning soap bar.',
                'item_type' => ProductItemType::Consumable,
                'tracks_inventory' => true,
                'is_sellable' => true,
                'is_purchasable' => true,
                'base_unit' => 'pc',
                'purchase_price' => 1200.00,
                'sale_price' => 1800.00,
                'reorder_level' => 40,
                'has_variants' => false,
                'parent_item' => null,
                'has_expiry' => false,
                'is_serialized' => false,
                'is_active' => true,
            ],
            [
                'category' => 'Office Supplies',
                'name' => 'A4 Paper Ream',
                'description' => 'Five-hundred sheet office printer paper ream.',
                'item_type' => ProductItemType::NonStockItem,
                'tracks_inventory' => false,
                'is_sellable' => true,
                'is_purchasable' => true,
                'base_unit' => null,
                'purchase_price' => 14500.00,
                'sale_price' => 18000.00,
                'reorder_level' => null,
                'has_variants' => false,
                'parent_item' => null,
                'has_expiry' => false,
                'is_serialized' => false,
                'is_active' => true,
            ],
            [
                'category' => 'Health & Personal Care',
                'name' => 'Hand Sanitizer 500ml',
                'description' => 'Alcohol-based hand sanitizer for retail and institutional use.',
                'item_type' => ProductItemType::StockItem,
                'tracks_inventory' => true,
                'is_sellable' => true,
                'is_purchasable' => true,
                'base_unit' => 'pc',
                'purchase_price' => 6500.00,
                'sale_price' => 8500.00,
                'reorder_level' => 15,
                'has_variants' => false,
                'parent_item' => null,
                'has_expiry' => true,
                'is_serialized' => false,
                'is_active' => true,
            ],
            [
                'category' => 'Animal Feed & Vet',
                'name' => 'Layers Mash 50kg',
                'description' => 'Bagged poultry feed for retail and farm use.',
                'item_type' => ProductItemType::StockItem,
                'tracks_inventory' => true,
                'is_sellable' => true,
                'is_purchasable' => true,
                'base_unit' => 'bag',
                'purchase_price' => 72000.00,
                'sale_price' => 83000.00,
                'reorder_level' => 8,
                'has_variants' => false,
                'parent_item' => null,
                'has_expiry' => false,
                'is_serialized' => false,
                'is_active' => true,
            ],
            [
                'category' => 'Hardware & Utilities',
                'name' => 'LED Bulb 12W',
                'description' => 'Energy-saving lighting bulb for household and shop use.',
                'item_type' => ProductItemType::StockItem,
                'tracks_inventory' => true,
                'is_sellable' => true,
                'is_purchasable' => true,
                'base_unit' => 'pc',
                'purchase_price' => 3800.00,
                'sale_price' => 5200.00,
                'reorder_level' => 20,
                'has_variants' => false,
                'parent_item' => null,
                'has_expiry' => false,
                'is_serialized' => false,
                'is_active' => true,
            ],
            [
                'category' => 'Services',
                'name' => 'Local Delivery Service',
                'description' => 'Delivery service billed per completed dispatch.',
                'item_type' => ProductItemType::Service,
                'tracks_inventory' => false,
                'is_sellable' => true,
                'is_purchasable' => false,
                'base_unit' => null,
                'purchase_price' => null,
                'sale_price' => 15000.00,
                'reorder_level' => null,
                'has_variants' => false,
                'parent_item' => null,
                'has_expiry' => false,
                'is_serialized' => false,
                'is_active' => true,
            ],
        ];
    }
}
