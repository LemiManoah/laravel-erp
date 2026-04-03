<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Purchasing\CreatePurchaseReceiptAction;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReceipt;
use App\Models\StockLocation;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

final class PurchaseReceiptSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = Supplier::query()->get()->keyBy('code');
        $products = InventoryItem::query()->get()->keyBy('name');
        $locations = StockLocation::query()->get()->keyBy('code');
        $orders = PurchaseOrder::query()->get()->keyBy('order_number');
        $action = app(CreatePurchaseReceiptAction::class);

        foreach ($this->receipts() as $receipt) {
            if (PurchaseReceipt::query()->where('receipt_number', $receipt['receipt_number'])->exists()) {
                continue;
            }

            $supplier = $suppliers->get($receipt['supplier_code']);
            $location = $locations->get($receipt['location_code']);
            $order = isset($receipt['order_number']) ? $orders->get($receipt['order_number']) : null;

            if ($supplier === null || $location === null) {
                continue;
            }

            $items = collect($receipt['items'])
                ->map(function (array $item) use ($products): ?array {
                    $product = $products->get($item['item_name']);

                    if ($product === null) {
                        return null;
                    }

                    $quantity = (float) $item['quantity'];
                    $unitCost = (float) $item['unit_cost'];

                    return [
                        'inventory_item_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_cost' => $unitCost,
                        'line_total' => round($quantity * $unitCost, 2),
                        'batch_number' => $item['batch_number'] ?? '',
                        'expiry_date' => $item['expiry_date'] ?? '',
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
                'receipt_number' => $receipt['receipt_number'],
                'supplier_id' => $supplier->id,
                'purchase_order_id' => $order?->id,
                'stock_location_id' => $location->id,
                'receipt_date' => $receipt['receipt_date'],
                'notes' => $receipt['notes'],
            ], $items);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function receipts(): array
    {
        return [
            [
                'receipt_number' => 'PRC-2026-001',
                'order_number' => 'PO-2026-001',
                'supplier_code' => 'SUP-001',
                'location_code' => 'STORE-01',
                'receipt_date' => '2026-03-31',
                'notes' => 'Weekly grocery restock.',
                'items' => [
                    ['item_name' => 'Rice 1kg Pack', 'quantity' => 25, 'unit_cost' => 2450.00],
                    ['item_name' => 'Orange Juice 1L', 'quantity' => 12, 'unit_cost' => 4150.00, 'batch_number' => 'OJ-2026-03-R1', 'expiry_date' => '2026-07-15'],
                ],
            ],
            [
                'receipt_number' => 'PRC-2026-002',
                'order_number' => 'PO-2026-002',
                'supplier_code' => 'SUP-002',
                'location_code' => 'MAIN-WH',
                'receipt_date' => '2026-03-31',
                'notes' => 'Farm supply replenishment.',
                'items' => [
                    ['item_name' => 'NPK Fertilizer 50kg', 'quantity' => 8, 'unit_cost' => 97500.00],
                    ['item_name' => 'Layers Mash 50kg', 'quantity' => 6, 'unit_cost' => 71000.00],
                ],
            ],
        ];
    }
}
