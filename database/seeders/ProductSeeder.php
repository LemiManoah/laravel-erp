<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

final class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ProductCategory::query()->get()->keyBy('name');

        foreach ($this->products() as $product) {
            $category = $product['category'];
            unset($product['category']);

            Product::query()->updateOrCreate(
                ['name' => $product['name']],
                [
                    ...$product,
                    'product_category_id' => $categories->get($category)?->id,
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
            ['category' => 'Suit (2-Piece)', 'name' => 'Standard Suit (2-Piece)', 'base_price' => 450.00, 'description' => 'Classic two-piece suit with jacket and trousers.', 'is_active' => true],
            ['category' => 'Suit (2-Piece)', 'name' => 'Premium Suit (2-Piece)', 'base_price' => 650.00, 'description' => 'Premium fabric two-piece suit.', 'is_active' => true],
            ['category' => 'Suit (2-Piece)', 'name' => 'Luxury Suit (2-Piece)', 'base_price' => 850.00, 'description' => 'Luxury Italian fabric two-piece suit.', 'is_active' => true],
            ['category' => 'Suit (3-Piece)', 'name' => 'Standard Suit (3-Piece)', 'base_price' => 550.00, 'description' => 'Classic three-piece suit.', 'is_active' => true],
            ['category' => 'Suit (3-Piece)', 'name' => 'Premium Suit (3-Piece)', 'base_price' => 750.00, 'description' => 'Premium fabric three-piece suit.', 'is_active' => true],
            ['category' => 'Suit (3-Piece)', 'name' => 'Luxury Suit (3-Piece)', 'base_price' => 950.00, 'description' => 'Luxury Italian fabric three-piece suit.', 'is_active' => true],
            ['category' => 'Jacket/Blazer', 'name' => 'Casual Blazer', 'base_price' => 250.00, 'description' => 'Smart casual blazer.', 'is_active' => true],
            ['category' => 'Jacket/Blazer', 'name' => 'Formal Jacket', 'base_price' => 300.00, 'description' => 'Formal event jacket.', 'is_active' => true],
            ['category' => 'Trouser', 'name' => 'Dress Trouser', 'base_price' => 120.00, 'description' => 'Standalone dress trousers.', 'is_active' => true],
            ['category' => 'Waistcoat', 'name' => 'Waistcoat Standard', 'base_price' => 100.00, 'description' => 'Standard waistcoat.', 'is_active' => true],
            ['category' => 'Waistcoat', 'name' => 'Waistcoat Premium', 'base_price' => 150.00, 'description' => 'Premium fabric waistcoat.', 'is_active' => true],
            ['category' => 'Shirt', 'name' => 'Custom Shirt', 'base_price' => 85.00, 'description' => 'Bespoke custom shirt.', 'is_active' => true],
            ['category' => 'Shirt', 'name' => 'Premium Shirt', 'base_price' => 120.00, 'description' => 'Premium fabric custom shirt.', 'is_active' => true],
            ['category' => 'Coat', 'name' => 'Overcoat', 'base_price' => 400.00, 'description' => 'Classic overcoat.', 'is_active' => true],
            ['category' => 'Coat', 'name' => 'Trench Coat', 'base_price' => 350.00, 'description' => 'Classic trench coat.', 'is_active' => true],
        ];
    }
}
