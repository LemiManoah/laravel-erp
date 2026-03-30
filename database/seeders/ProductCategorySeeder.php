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
            ['name' => 'Suit (2-Piece)', 'description' => 'Two-piece suits including jacket and trousers.', 'is_active' => true],
            ['name' => 'Suit (3-Piece)', 'description' => 'Three-piece suits including jacket, trousers, and waistcoat.', 'is_active' => true],
            ['name' => 'Jacket/Blazer', 'description' => 'Single jackets and blazers.', 'is_active' => true],
            ['name' => 'Trouser', 'description' => 'Standalone trousers and pants.', 'is_active' => true],
            ['name' => 'Waistcoat', 'description' => 'Waistcoats and vests.', 'is_active' => true],
            ['name' => 'Shirt', 'description' => 'Custom shirts and blouses.', 'is_active' => true],
            ['name' => 'Coat', 'description' => 'Overcoats and long coats.', 'is_active' => true],
            ['name' => 'Alterations', 'description' => 'Alteration services.', 'is_active' => true],
        ];
    }
}
