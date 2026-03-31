<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UnitOfMeasure;
use Illuminate\Database\Seeder;

final class UnitOfMeasureSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = tenant('id');

        $units = [
            ['name' => 'Piece', 'abbreviation' => 'pc', 'is_active' => true],
            ['name' => 'Kilogram', 'abbreviation' => 'kg', 'is_active' => true],
            ['name' => 'Gram', 'abbreviation' => 'g', 'is_active' => true],
            ['name' => 'Meter', 'abbreviation' => 'm', 'is_active' => true],
            ['name' => 'Centimeter', 'abbreviation' => 'cm', 'is_active' => true],
            ['name' => 'Liter', 'abbreviation' => 'l', 'is_active' => true],
            ['name' => 'Milliliter', 'abbreviation' => 'ml', 'is_active' => true],
            ['name' => 'Box', 'abbreviation' => 'box', 'is_active' => true],
            ['name' => 'Bag', 'abbreviation' => 'bag', 'is_active' => true],
            ['name' => 'Dozen', 'abbreviation' => 'doz', 'is_active' => true],
            ['name' => 'Set', 'abbreviation' => 'set', 'is_active' => true],
        ];

        foreach ($units as $unit) {
            UnitOfMeasure::query()->updateOrCreate(
                ['tenant_id' => $tenantId, 'name' => $unit['name']],
                [...$unit, 'tenant_id' => $tenantId]
            );
        }
    }
}
