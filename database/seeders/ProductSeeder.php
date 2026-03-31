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

        foreach ($this->products() as $productData) {
            $category = $productData['category'];
            $unit = $productData['base_unit'] ?? null;
            $sellingPrice = $productData['base_price'] ?? null;
            $buyingPrice = $productData['buying_price'] ?? null;

            unset($productData['category'], $productData['base_unit'], $productData['base_price'], $productData['buying_price']);

            $product = Product::query()->updateOrCreate(
                ['sku' => $productData['sku']],
                [
                    ...$productData,
                    'product_category_id' => $categories->get($category)?->id,
                    'base_unit_id' => $unit === null ? null : $units->get($unit)?->id,
                ],
            );

            $product->defaultPrice()->updateOrCreate(
                ['tenant_id' => tenant('id'), 'product_id' => $product->id],
                [
                    'selling_price' => $sellingPrice,
                    'buying_price' => $buyingPrice,
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
                'sku' => 'GRO-RICE-001',
                'barcode' => '100000000001',
                'name' => 'Rice 1kg Pack',
                'description' => 'Pre-packed polished rice for retail sale.',
                'item_type' => ProductItemType::StockItem,
                'tracks_inventory' => true,
                'is_sellable' => true,
                'is_purchasable' => true,
                'base_unit' => 'pc',
                'buying_price' => 2500.00,
                'base_price' => 3200.00,
                'reorder_level' => 20,
                'reorder_quantity' => 80,
                'has_variants' => false,
                'parent_item_id' => null,
                'has_expiry' => false,
                'allow_negative_stock' => false,
                'is_serialized' => false,
                'is_active' => true,
            ],
            [
                'category' => 'Fresh Produce',
                'sku' => 'FRS-TOM-001',
                'barcode' => '100000000002',
                'name' => 'Tomatoes Crate',
                'description' => 'Fresh tomatoes sold by crate for market and shop restocking.',
                'item_type' => ProductItemType::FinishedGood,
                'tracks_inventory' => true,
                'is_sellable' => true,
                'is_purchasable' => true,
                'base_unit' => 'box',
                'buying_price' => 28000.00,
                'base_price' => 35000.00,
                'reorder_level' => 5,
                'reorder_quantity' => 15,
                'has_variants' => false,
                'parent_item_id' => null,
                'has_expiry' => true,
                'allow_negative_stock' => false,
                'is_serialized' => false,
                'is_active' => true,
            ],
            [
                'category' => 'Beverages',
                'sku' => 'BEV-JCE-001',
                'barcode' => '100000000003',
                'name' => 'Orange Juice 1L',
                'description' => 'Packaged orange juice in one-litre cartons.',
                'item_type' => ProductItemType::StockItem,
                'tracks_inventory' => true,
                'is_sellable' => true,
                'is_purchasable' => true,
                'base_unit' => 'pc',
                'buying_price' => 4200.00,
                'base_price' => 5500.00,
                'reorder_level' => 12,
                'reorder_quantity' => 48,
                'has_variants' => false,
                'parent_item_id' => null,
                'has_expiry' => true,
                'allow_negative_stock' => false,
                'is_serialized' => false,
                'is_active' => true,
            ],
            [
                'category' => 'Farm Inputs',
                'sku' => 'FAR-FER-001',
                'barcode' => '100000000004',
                'name' => 'NPK Fertilizer 50kg',
                'description' => 'General-purpose fertilizer for farm and agro-input stores.',
                'item_type' => ProductItemType::RawMaterial,
                'tracks_inventory' => true,
                'is_sellable' => true,
                'is_purchasable' => true,
                'base_unit' => 'bag',
                'buying_price' => 98000.00,
                'base_price' => 115000.00,
                'reorder_level' => 10,
                'reorder_quantity' => 30,
                'has_variants' => false,
                'parent_item_id' => null,
                'has_expiry' => false,
                'allow_negative_stock' => false,
                'is_serialized' => false,
                'is_active' => true,
            ],
            [
                'category' => 'Household Goods',
                'sku' => 'HOU-SOA-001',
                'barcode' => '100000000005',
                'name' => 'Laundry Soap Bar',
                'description' => 'Everyday household cleaning soap bar.',
                'item_type' => ProductItemType::Consumable,
                'tracks_inventory' => true,
                'is_sellable' => true,
                'is_purchasable' => true,
                'base_unit' => 'pc',
                'buying_price' => 1200.00,
                'base_price' => 1800.00,
                'reorder_level' => 40,
                'reorder_quantity' => 120,
                'has_variants' => false,
                'parent_item_id' => null,
                'has_expiry' => false,
                'allow_negative_stock' => false,
                'is_serialized' => false,
                'is_active' => true,
            ],
            [
                'category' => 'Office Supplies',
                'sku' => 'OFF-PPR-001',
                'barcode' => '100000000006',
                'name' => 'A4 Paper Ream',
                'description' => 'Five-hundred sheet office printer paper ream.',
                'item_type' => ProductItemType::NonStockItem,
                'tracks_inventory' => false,
                'is_sellable' => true,
                'is_purchasable' => true,
                'base_unit' => null,
                'buying_price' => 14500.00,
                'base_price' => 18000.00,
                'reorder_level' => null,
                'reorder_quantity' => null,
                'has_variants' => false,
                'parent_item_id' => null,
                'has_expiry' => false,
                'allow_negative_stock' => false,
                'is_serialized' => false,
                'is_active' => true,
            ],
            [
                'category' => 'Health & Personal Care',
                'sku' => 'HPC-SAN-001',
                'barcode' => '100000000007',
                'name' => 'Hand Sanitizer 500ml',
                'description' => 'Alcohol-based hand sanitizer for retail and institutional use.',
                'item_type' => ProductItemType::StockItem,
                'tracks_inventory' => true,
                'is_sellable' => true,
                'is_purchasable' => true,
                'base_unit' => 'pc',
                'buying_price' => 6500.00,
                'base_price' => 8500.00,
                'reorder_level' => 15,
                'reorder_quantity' => 40,
                'has_variants' => false,
                'parent_item_id' => null,
                'has_expiry' => true,
                'allow_negative_stock' => false,
                'is_serialized' => false,
                'is_active' => true,
            ],
            [
                'category' => 'Animal Feed & Vet',
                'sku' => 'ANM-FED-001',
                'barcode' => '100000000008',
                'name' => 'Layers Mash 50kg',
                'description' => 'Bagged poultry feed for retail and farm use.',
                'item_type' => ProductItemType::StockItem,
                'tracks_inventory' => true,
                'is_sellable' => true,
                'is_purchasable' => true,
                'base_unit' => 'bag',
                'buying_price' => 72000.00,
                'base_price' => 83000.00,
                'reorder_level' => 8,
                'reorder_quantity' => 24,
                'has_variants' => false,
                'parent_item_id' => null,
                'has_expiry' => false,
                'allow_negative_stock' => false,
                'is_serialized' => false,
                'is_active' => true,
            ],
            [
                'category' => 'Hardware & Utilities',
                'sku' => 'HRD-BLB-001',
                'barcode' => '100000000009',
                'name' => 'LED Bulb 12W',
                'description' => 'Energy-saving lighting bulb for household and shop use.',
                'item_type' => ProductItemType::StockItem,
                'tracks_inventory' => true,
                'is_sellable' => true,
                'is_purchasable' => true,
                'base_unit' => 'pc',
                'buying_price' => 3800.00,
                'base_price' => 5200.00,
                'reorder_level' => 20,
                'reorder_quantity' => 60,
                'has_variants' => false,
                'parent_item_id' => null,
                'has_expiry' => false,
                'allow_negative_stock' => false,
                'is_serialized' => false,
                'is_active' => true,
            ],
            [
                'category' => 'Services',
                'sku' => 'SRV-DEL-001',
                'barcode' => null,
                'name' => 'Local Delivery Service',
                'description' => 'Delivery service billed per completed dispatch.',
                'item_type' => ProductItemType::Service,
                'tracks_inventory' => false,
                'is_sellable' => true,
                'is_purchasable' => false,
                'base_unit' => null,
                'buying_price' => null,
                'base_price' => 15000.00,
                'reorder_level' => null,
                'reorder_quantity' => null,
                'has_variants' => false,
                'parent_item_id' => null,
                'has_expiry' => false,
                'allow_negative_stock' => false,
                'is_serialized' => false,
                'is_active' => true,
            ],
        ];
    }
}
