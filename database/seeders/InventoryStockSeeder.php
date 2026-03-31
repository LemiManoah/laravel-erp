<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\StockLocation;
use Illuminate\Database\Seeder;

final class InventoryStockSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::query()->get()->keyBy('sku');
        $locations = StockLocation::query()->get()->keyBy('code');

        foreach ($this->stocks() as $stockData) {
            $product = $products->get($stockData['sku']);
            $location = $locations->get($stockData['location_code']);

            if ($product === null || $location === null) {
                continue;
            }

            InventoryStock::query()->updateOrCreate(
                [
                    'tenant_id' => tenant('id'),
                    'product_id' => $product->id,
                    'location_id' => $location->id,
                    'batch_number' => $stockData['batch_number'],
                ],
                [
                    'expiry_date' => $stockData['expiry_date'],
                    'received_at' => $stockData['received_at'],
                    'quantity_on_hand' => $stockData['quantity_on_hand'],
                    'unit_cost' => $stockData['unit_cost'],
                    'notes' => $stockData['notes'],
                ],
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function stocks(): array
    {
        return [
            ['sku' => 'GRO-RICE-001', 'location_code' => 'STORE-01', 'batch_number' => null, 'expiry_date' => null, 'received_at' => '2026-03-31', 'quantity_on_hand' => 60, 'unit_cost' => 2500.00, 'notes' => 'Initial stock row'],
            ['sku' => 'FRS-TOM-001', 'location_code' => 'STORE-01', 'batch_number' => 'TOM-2026-03-A', 'expiry_date' => '2026-04-05', 'received_at' => '2026-03-31', 'quantity_on_hand' => 8, 'unit_cost' => 28000.00, 'notes' => 'Initial expiry stock row'],
            ['sku' => 'BEV-JCE-001', 'location_code' => 'STORE-01', 'batch_number' => 'OJ-2026-03-B', 'expiry_date' => '2026-06-30', 'received_at' => '2026-03-31', 'quantity_on_hand' => 24, 'unit_cost' => 4200.00, 'notes' => 'Initial expiry stock row'],
            ['sku' => 'FAR-FER-001', 'location_code' => 'MAIN-WH', 'batch_number' => null, 'expiry_date' => null, 'received_at' => '2026-03-31', 'quantity_on_hand' => 18, 'unit_cost' => 98000.00, 'notes' => 'Warehouse stock'],
            ['sku' => 'HOU-SOA-001', 'location_code' => 'STORE-01', 'batch_number' => null, 'expiry_date' => null, 'received_at' => '2026-03-31', 'quantity_on_hand' => 90, 'unit_cost' => 1200.00, 'notes' => 'Shelf stock'],
            ['sku' => 'HPC-SAN-001', 'location_code' => 'STORE-01', 'batch_number' => 'SAN-2026-03-C', 'expiry_date' => '2027-01-31', 'received_at' => '2026-03-31', 'quantity_on_hand' => 30, 'unit_cost' => 6500.00, 'notes' => 'Initial expiry stock row'],
            ['sku' => 'ANM-FED-001', 'location_code' => 'MAIN-WH', 'batch_number' => null, 'expiry_date' => null, 'received_at' => '2026-03-31', 'quantity_on_hand' => 12, 'unit_cost' => 72000.00, 'notes' => 'Feed stock'],
            ['sku' => 'HRD-BLB-001', 'location_code' => 'STORE-01', 'batch_number' => null, 'expiry_date' => null, 'received_at' => '2026-03-31', 'quantity_on_hand' => 35, 'unit_cost' => 3800.00, 'notes' => 'Retail stock'],
        ];
    }
}
