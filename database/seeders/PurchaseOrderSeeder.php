<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Purchasing\CreatePurchaseOrderAction;
use App\Enums\PurchaseOrderStatus;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockLocation;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

final class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = Supplier::query()->get()->keyBy('code');
        $products = Product::query()->get()->keyBy('name');
        $locations = StockLocation::query()->get()->keyBy('code');
        $action = app(CreatePurchaseOrderAction::class);

        foreach ($this->orders() as $order) {
            if (PurchaseOrder::query()->where('order_number', $order['order_number'])->exists()) {
                continue;
            }

            $supplier = $suppliers->get($order['supplier_code']);
            $location = $locations->get($order['location_code']);

            if ($supplier === null || $location === null) {
                continue;
            }

            $items = collect($order['items'])
                ->map(function (array $item) use ($products): ?array {
                    $product = $products->get($item['item_name']);

                    if ($product === null) {
                        return null;
                    }

                    $quantity = (float) $item['quantity'];
                    $unitCost = (float) $item['unit_cost'];

                    return [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_cost' => $unitCost,
                        'line_total' => round($quantity * $unitCost, 2),
                        'notes' => $item['notes'] ?? '',
                    ];
                })
                ->filter()
                ->values()
                ->all();

            if ($items === []) {
                continue;
            }

            $action->handle([
                'order_number' => $order['order_number'],
                'supplier_id' => $supplier->id,
                'stock_location_id' => $location->id,
                'order_date' => $order['order_date'],
                'expected_date' => $order['expected_date'],
                'status' => PurchaseOrderStatus::from($order['status']),
                'notes' => $order['notes'],
            ], $items);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function orders(): array
    {
        return [
            [
                'order_number' => 'PO-2026-001',
                'supplier_code' => 'SUP-001',
                'location_code' => 'STORE-01',
                'order_date' => '2026-03-31',
                'expected_date' => '2026-04-02',
                'status' => 'ordered',
                'notes' => 'Weekend grocery top-up.',
                'items' => [
                    ['item_name' => 'Rice 1kg Pack', 'quantity' => 40, 'unit_cost' => 2450.00],
                    ['item_name' => 'Laundry Soap Bar', 'quantity' => 50, 'unit_cost' => 1180.00],
                ],
            ],
            [
                'order_number' => 'PO-2026-002',
                'supplier_code' => 'SUP-002',
                'location_code' => 'MAIN-WH',
                'order_date' => '2026-03-31',
                'expected_date' => '2026-04-04',
                'status' => 'draft',
                'notes' => 'Planned farm input restock.',
                'items' => [
                    ['item_name' => 'NPK Fertilizer 50kg', 'quantity' => 12, 'unit_cost' => 97000.00],
                    ['item_name' => 'Layers Mash 50kg', 'quantity' => 10, 'unit_cost' => 70500.00],
                ],
            ],
        ];
    }
}
