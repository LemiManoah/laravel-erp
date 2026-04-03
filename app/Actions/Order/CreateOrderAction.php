<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Models\Order;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final readonly class CreateOrderAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Order
    {
        return DB::transaction(function () use ($data): Order {
            $order = new Order($data);
            $order->order_number = 'ORD-'.strtoupper(uniqid());
            $order->status = 'confirmed';
            $order->created_by = Auth::id();
            $order->save();

            foreach ($data['items'] as $item) {
                $itemData = $this->processItem($item);
                $order->items()->create($itemData);
            }

            return $order;
        });
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function processItem(array $item): array
    {
        $inventoryItemId = $item['inventory_item_id'] ?? null;

        if ($inventoryItemId === 'custom') {
            $item['garment_type'] = $item['garment_type'] ?? '';
            $item['inventory_item_id'] = null;
        } elseif ($inventoryItemId) {
            $inventoryItem = InventoryItem::find($inventoryItemId);
            if ($inventoryItem) {
                $item['garment_type'] = $inventoryItem->name;
                $item['inventory_item_id'] = (int) $inventoryItemId;
            }
        }

        return $item;
    }
}
