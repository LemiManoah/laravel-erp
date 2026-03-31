<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

final class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->suppliers() as $supplier) {
            Supplier::query()->updateOrCreate(
                ['tenant_id' => tenant('id'), 'name' => $supplier['name']],
                $supplier,
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function suppliers(): array
    {
        return [
            [
                'tenant_id' => tenant('id'),
                'name' => 'Green Harvest Supplies',
                'code' => 'SUP-001',
                'contact_person' => 'Martha K.',
                'email' => 'orders@greenharvest.test',
                'phone' => '+256700100001',
                'address' => 'Kampala Industrial Area',
                'tax_number' => 'TX-001-GHS',
                'notes' => 'Main supplier for produce and grocery restocking.',
                'is_active' => true,
            ],
            [
                'tenant_id' => tenant('id'),
                'name' => 'AgroVet Source',
                'code' => 'SUP-002',
                'contact_person' => 'Paul N.',
                'email' => 'supply@agrovet.test',
                'phone' => '+256700100002',
                'address' => 'Masaka Road',
                'tax_number' => 'TX-002-AGS',
                'notes' => 'Feeds, fertilizer, and farm input supplier.',
                'is_active' => true,
            ],
            [
                'tenant_id' => tenant('id'),
                'name' => 'City Wholesale Traders',
                'code' => 'SUP-003',
                'contact_person' => 'Susan A.',
                'email' => 'sales@citywholesale.test',
                'phone' => '+256700100003',
                'address' => 'Nakasero Market Street',
                'tax_number' => 'TX-003-CWT',
                'notes' => 'Fallback supplier for beverages and household goods.',
                'is_active' => true,
            ],
        ];
    }
}
