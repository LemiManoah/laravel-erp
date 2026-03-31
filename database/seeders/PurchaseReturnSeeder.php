<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Purchasing\CreatePurchaseReturnAction;
use App\Models\InventoryStock;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseReturn;
use Illuminate\Database\Seeder;

final class PurchaseReturnSeeder extends Seeder
{
    public function run(): void
    {
        $receipt = PurchaseReceipt::query()->where('receipt_number', 'PRC-2026-001')->first();

        if ($receipt === null || PurchaseReturn::query()->where('return_number', 'PRN-2026-001')->exists()) {
            return;
        }

        $stock = InventoryStock::query()
            ->where('product_id', $receipt->items()->first()?->product_id)
            ->where('location_id', $receipt->stock_location_id)
            ->first();

        if ($stock === null) {
            return;
        }

        app(CreatePurchaseReturnAction::class)->handle([
            'return_number' => 'PRN-2026-001',
            'supplier_id' => $receipt->supplier_id,
            'purchase_receipt_id' => $receipt->id,
            'stock_location_id' => $receipt->stock_location_id,
            'return_date' => '2026-03-31',
            'notes' => 'Damaged cartons returned to supplier.',
        ], [[
            'product_id' => $stock->product_id,
            'inventory_stock_id' => $stock->id,
            'quantity' => 2,
            'unit_cost' => (float) ($stock->unit_cost ?? 0),
            'line_total' => round(2 * (float) ($stock->unit_cost ?? 0), 2),
            'notes' => 'Returned due to packaging damage.',
        ]]);
    }
}
