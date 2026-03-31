<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\StockLocationType;
use App\Models\StockLocation;
use Illuminate\Database\Seeder;

final class StockLocationSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = tenant('id');

        $locations = [
            [
                'name' => 'Main Warehouse',
                'code' => 'MAIN-WH',
                'location_type' => StockLocationType::Warehouse,
                'address' => '123 Main St, Warehouse District',
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Store Front',
                'code' => 'STORE-01',
                'location_type' => StockLocationType::Store,
                'address' => '456 Retail Ave',
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Returns Processing',
                'code' => 'RET-01',
                'location_type' => StockLocationType::Warehouse,
                'address' => '123 Main St, Loading Dock B',
                'is_default' => false,
                'is_active' => true,
            ],
        ];

        foreach ($locations as $location) {
            StockLocation::query()->updateOrCreate(
                ['tenant_id' => $tenantId, 'code' => $location['code']],
                [...$location, 'tenant_id' => $tenantId]
            );
        }
    }
}
