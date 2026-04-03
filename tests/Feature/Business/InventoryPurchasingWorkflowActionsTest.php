<?php

declare(strict_types=1);

use App\Actions\Inventory\RecordInventoryMovementAction;
use App\Actions\Inventory\TransferInventoryAction;
use App\Actions\Purchasing\CreatePurchaseOrderAction;
use App\Actions\Purchasing\CreatePurchaseReceiptAction;
use App\Actions\Purchasing\CreatePurchaseReturnAction;
use App\Enums\InventoryDirection;
use App\Enums\InventoryMovementType;
use App\Enums\InventoryItemType;
use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseReceiptStatus;
use App\Enums\StockLocationType;
use App\Models\Currency;
use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseReturn;
use App\Models\StockLocation;
use App\Models\Supplier;
use App\Models\UnitOfMeasure;
use App\Models\User;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->currency = Currency::query()->create([
        'name' => 'Ugandan Shilling',
        'code' => 'UGX',
        'symbol' => 'USh',
        'decimal_places' => 0,
        'exchange_rate' => 1,
        'is_default' => true,
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->unit = inventoryWorkflowUnit();
    $this->sourceLocation = inventoryWorkflowLocation('Main Warehouse', 'MAIN', true);
    $this->destinationLocation = inventoryWorkflowLocation('Retail Store', 'STORE');
    $this->supplier = inventoryWorkflowSupplier();
});

it('creates paired transfer movements and moves stock between locations', function (): void {
    $product = inventoryWorkflowProduct($this->unit, [
        'name' => 'Transfer InventoryItem',
    ]);

    $sourceStock = InventoryStock::query()->create([
        'inventory_item_id' => $product->id,
        'location_id' => $this->sourceLocation->id,
        'quantity_on_hand' => 10,
        'received_at' => now()->subDay()->toDateString(),
        'unit_cost' => 15,
    ]);

    $movements = app(TransferInventoryAction::class)->handle(
        $product,
        $this->sourceLocation,
        $this->destinationLocation,
        4,
        [
            'inventory_stock_id' => $sourceStock->id,
            'movement_date' => now(),
            'notes' => 'Store restock',
        ],
    );

    $sourceStock->refresh();
    $destinationStock = InventoryStock::query()
        ->where('inventory_item_id', $product->id)
        ->where('location_id', $this->destinationLocation->id)
        ->firstOrFail();

    expect($movements['out']->movement_type)->toBe(InventoryMovementType::TransferOut);
    expect($movements['out']->direction)->toBe(InventoryDirection::Out);
    expect($movements['in']->movement_type)->toBe(InventoryMovementType::TransferIn);
    expect($movements['in']->direction)->toBe(InventoryDirection::In);
    expect((float) $sourceStock->quantity_on_hand)->toBe(6.0);
    expect((float) $destinationStock->quantity_on_hand)->toBe(4.0);
});

it('rejects sale issues from expired stock rows', function (): void {
    $product = inventoryWorkflowProduct($this->unit, [
        'name' => 'Expiring Fabric',
        'has_expiry' => true,
    ]);

    $expiredStock = InventoryStock::query()->create([
        'inventory_item_id' => $product->id,
        'location_id' => $this->sourceLocation->id,
        'batch_number' => 'BATCH-OLD',
        'expiry_date' => now()->subDay()->toDateString(),
        'received_at' => now()->subWeek()->toDateString(),
        'quantity_on_hand' => 5,
        'unit_cost' => 8,
    ]);

    try {
        app(RecordInventoryMovementAction::class)->handle(
            $product,
            InventoryMovementType::SaleIssue,
            1,
            [
                'location_id' => $this->sourceLocation->id,
                'inventory_stock_id' => $expiredStock->id,
                'movement_date' => now(),
            ],
        );

        $this->fail('Expected issuing expired stock to throw a validation exception.');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey('inventory_stock_id');
    }

    expect((float) $expiredStock->fresh()->quantity_on_hand)->toBe(5.0);
    expect(InventoryMovement::query()->count())->toBe(0);
});

it('creates purchase orders and receipts and marks the linked order as received', function (): void {
    $product = inventoryWorkflowProduct($this->unit, [
        'name' => 'Purchase Receipt InventoryItem',
    ]);

    $order = app(CreatePurchaseOrderAction::class)->handle([
        'order_number' => 'PO-BUS-0001',
        'supplier_id' => $this->supplier->id,
        'stock_location_id' => $this->sourceLocation->id,
        'order_date' => now()->toDateString(),
        'created_by' => $this->user->id,
    ], [[
        'inventory_item_id' => $product->id,
        'quantity' => 3,
        'unit_cost' => 12,
        'line_total' => 36,
        'notes' => 'Fabric roll',
    ]]);

    $receipt = app(CreatePurchaseReceiptAction::class)->handle([
        'receipt_number' => 'PR-BUS-0001',
        'supplier_id' => $this->supplier->id,
        'purchase_order_id' => $order->id,
        'stock_location_id' => $this->sourceLocation->id,
        'receipt_date' => now()->toDateString(),
        'created_by' => $this->user->id,
    ], [[
        'inventory_item_id' => $product->id,
        'quantity' => 3,
        'unit_cost' => 12,
        'line_total' => 36,
        'batch_number' => '',
        'expiry_date' => '',
        'notes' => 'Received in good condition',
    ]]);

    $stock = InventoryStock::query()
        ->where('inventory_item_id', $product->id)
        ->where('location_id', $this->sourceLocation->id)
        ->firstOrFail();

    expect($order->subtotal_amount)->toBe('36.00');
    expect($order->fresh()->status)->toBe(PurchaseOrderStatus::Received);
    expect($receipt->status)->toBe(PurchaseReceiptStatus::Posted);
    expect($receipt->items)->toHaveCount(1);
    expect((float) $stock->quantity_on_hand)->toBe(3.0);
    expect(PurchaseOrder::query()->count())->toBe(1);
    expect(PurchaseReceipt::query()->count())->toBe(1);
});

it('creates purchase returns and deducts the returned quantity from the stock row', function (): void {
    $product = inventoryWorkflowProduct($this->unit, [
        'name' => 'Purchase Return InventoryItem',
    ]);

    $receipt = app(CreatePurchaseReceiptAction::class)->handle([
        'receipt_number' => 'PR-BUS-0002',
        'supplier_id' => $this->supplier->id,
        'stock_location_id' => $this->sourceLocation->id,
        'receipt_date' => now()->toDateString(),
        'created_by' => $this->user->id,
    ], [[
        'inventory_item_id' => $product->id,
        'quantity' => 5,
        'unit_cost' => 10,
        'line_total' => 50,
        'batch_number' => '',
        'expiry_date' => '',
        'notes' => 'Initial receipt',
    ]]);

    $stock = InventoryStock::query()
        ->where('inventory_item_id', $product->id)
        ->where('location_id', $this->sourceLocation->id)
        ->firstOrFail();

    $return = app(CreatePurchaseReturnAction::class)->handle([
        'return_number' => 'RET-BUS-0001',
        'supplier_id' => $this->supplier->id,
        'purchase_receipt_id' => $receipt->id,
        'stock_location_id' => $this->sourceLocation->id,
        'return_date' => now()->toDateString(),
        'created_by' => $this->user->id,
    ], [[
        'inventory_item_id' => $product->id,
        'inventory_stock_id' => $stock->id,
        'quantity' => 2,
        'unit_cost' => 10,
        'line_total' => 20,
        'notes' => 'Damaged on arrival',
    ]]);

    expect($return->items)->toHaveCount(1);
    expect($return->purchaseReceipt?->is($receipt))->toBeTrue();
    expect((float) $stock->fresh()->quantity_on_hand)->toBe(3.0);
    expect(PurchaseReturn::query()->count())->toBe(1);
    expect(InventoryMovement::query()->where('movement_type', InventoryMovementType::PurchaseReturn)->count())->toBe(1);
});

function inventoryWorkflowUnit(): UnitOfMeasure
{
    return UnitOfMeasure::query()->create([
        'name' => 'Piece',
        'abbreviation' => 'pc',
        'is_active' => true,
    ]);
}

function inventoryWorkflowLocation(string $name, string $code, bool $isDefault = false): StockLocation
{
    return StockLocation::query()->create([
        'name' => $name,
        'code' => $code,
        'location_type' => StockLocationType::Warehouse,
        'is_default' => $isDefault,
        'is_active' => true,
    ]);
}

function inventoryWorkflowSupplier(): Supplier
{
    static $counter = 0;
    $counter++;

    return Supplier::query()->create([
        'name' => sprintf('Supplier %d', $counter),
        'code' => sprintf('SUP-%03d', $counter),
        'email' => sprintf('supplier%d@example.test', $counter),
        'phone' => sprintf('+256710000%03d', $counter),
        'is_active' => true,
    ]);
}

function inventoryWorkflowProduct(UnitOfMeasure $unit, array $overrides = []): InventoryItem
{
    static $counter = 0;
    $counter++;

    return InventoryItem::query()->create([
        'name' => sprintf('Inventory InventoryItem %d', $counter),
        'item_type' => InventoryItemType::StockItem,
        'tracks_inventory' => true,
        'is_sellable' => true,
        'is_purchasable' => true,
        'base_unit_id' => $unit->id,
        'has_expiry' => false,
        'is_serialized' => false,
        'has_variants' => false,
        'is_active' => true,
        ...$overrides,
    ]);
}
