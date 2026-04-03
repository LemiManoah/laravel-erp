<?php

declare(strict_types=1);

use App\Actions\Customer\DeleteCustomerAction;
use App\Actions\Inventory\IssueInvoiceInventoryAction;
use App\Actions\Invoice\CancelInvoiceAction;
use App\Actions\Measurement\CreateMeasurementAction;
use App\Enums\InventoryMovementType;
use App\Enums\InventoryItemType;
use App\Enums\StockLocationType;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Measurement;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\InventoryItem;
use App\Models\StockLocation;
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

    $this->unit = invoiceMaintenanceUnit();
    $this->location = StockLocation::query()->create([
        'name' => 'Default Warehouse',
        'code' => 'DEF',
        'location_type' => StockLocationType::Warehouse,
        'is_default' => true,
        'is_active' => true,
    ]);

    $this->paymentMethod = PaymentMethod::query()->create([
        'name' => 'Cash',
        'slug' => 'cash',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->customer = invoiceMaintenanceCustomer($this->user);
});

it('issues invoice inventory from the default stock location when the invoice has none set', function (): void {
    $product = invoiceMaintenanceProduct($this->unit, [
        'name' => 'Tailoring Material',
    ]);

    $stock = InventoryStock::query()->create([
        'inventory_item_id' => $product->id,
        'location_id' => $this->location->id,
        'quantity_on_hand' => 5,
        'received_at' => now()->subDay()->toDateString(),
        'unit_cost' => 14,
    ]);

    $invoice = invoiceMaintenanceInvoice($this->customer, $this->currency, $this->user, [
        'stock_location_id' => null,
        'issued_at' => now(),
        'status' => 'issued',
    ]);

    InvoiceItem::query()->create([
        'invoice_id' => $invoice->id,
        'inventory_item_id' => $product->id,
        'item_name' => 'Waistcoat',
        'quantity' => 2,
        'unit_price' => 30,
        'line_total' => 60,
    ]);

    app(IssueInvoiceInventoryAction::class)->handle($invoice);

    expect($invoice->fresh()->stock_location_id)->toBe($this->location->id);
    expect((float) $stock->fresh()->quantity_on_hand)->toBe(3.0);
    expect(InventoryMovement::query()->where('movement_type', InventoryMovementType::SaleIssue)->count())->toBe(1);
});

it('cancels an invoice and restores previously issued inventory', function (): void {
    $product = invoiceMaintenanceProduct($this->unit, [
        'name' => 'Suit Fabric',
    ]);

    $stock = InventoryStock::query()->create([
        'inventory_item_id' => $product->id,
        'location_id' => $this->location->id,
        'quantity_on_hand' => 6,
        'received_at' => now()->subDay()->toDateString(),
        'unit_cost' => 20,
    ]);

    $invoice = invoiceMaintenanceInvoice($this->customer, $this->currency, $this->user, [
        'stock_location_id' => $this->location->id,
        'issued_at' => now(),
        'status' => 'issued',
        'total_amount' => 120,
        'balance_due' => 120,
    ]);

    InvoiceItem::query()->create([
        'invoice_id' => $invoice->id,
        'inventory_item_id' => $product->id,
        'item_name' => 'Three-piece suit',
        'quantity' => 2,
        'unit_price' => 60,
        'line_total' => 120,
    ]);

    app(IssueInvoiceInventoryAction::class)->handle($invoice);

    $cancelled = app(CancelInvoiceAction::class)->handle($invoice->fresh(), 'Customer cancelled the booking.');

    expect($cancelled->status)->toBe('cancelled');
    expect($cancelled->cancellation_reason)->toBe('Customer cancelled the booking.');
    expect((float) $stock->fresh()->quantity_on_hand)->toBe(6.0);
    expect(InventoryMovement::query()->where('movement_type', InventoryMovementType::SaleIssue)->count())->toBe(1);
    expect(InventoryMovement::query()->where('movement_type', InventoryMovementType::SalesReturn)->count())->toBe(1);
});

it('prevents cancelling invoices that already have valid payments', function (): void {
    $invoice = invoiceMaintenanceInvoice($this->customer, $this->currency, $this->user, [
        'status' => 'partially_paid',
        'issued_at' => now()->subDay(),
        'total_amount' => 100,
        'amount_paid' => 25,
        'balance_due' => 75,
    ]);

    Payment::query()->create([
        'invoice_id' => $invoice->id,
        'currency_id' => $this->currency->id,
        'payment_date' => now()->toDateString(),
        'amount' => 25,
        'payment_method_id' => $this->paymentMethod->id,
        'payment_method' => $this->paymentMethod->name,
        'reference_number' => 'PMT-CANCEL-001',
        'status' => 'valid',
        'received_by' => $this->user->id,
    ]);

    try {
        app(CancelInvoiceAction::class)->handle($invoice, 'Attempted cancellation.');

        $this->fail('Expected cancelling an invoice with valid payments to throw a validation exception.');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey('invoice');
    }
});

it('prevents deleting customers that have dependent business records', function (): void {
    Order::query()->create([
        'order_number' => 'ORD-DELETE-0001',
        'customer_id' => $this->customer->id,
        'currency_id' => $this->currency->id,
        'order_date' => now()->toDateString(),
        'status' => 'draft',
        'priority' => 'medium',
        'created_by' => $this->user->id,
    ]);

    try {
        app(DeleteCustomerAction::class)->handle($this->customer);

        $this->fail('Expected deleting a customer with related records to throw a validation exception.');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey('customer');
    }
});

it('marks only the newest current measurement as current', function (): void {
    $first = app(CreateMeasurementAction::class)->handle($this->customer, [
        'measurement_date' => now()->subWeek()->toDateString(),
        'chest' => 40,
        'waist' => 32,
        'is_current' => true,
    ]);

    $second = app(CreateMeasurementAction::class)->handle($this->customer, [
        'measurement_date' => now()->toDateString(),
        'chest' => 41,
        'waist' => 33,
        'is_current' => true,
    ]);

    expect($first->fresh()->is_current)->toBeFalse();
    expect($second->fresh()->is_current)->toBeTrue();
    expect(Measurement::query()->where('customer_id', $this->customer->id)->where('is_current', true)->count())->toBe(1);
});

function invoiceMaintenanceUnit(): UnitOfMeasure
{
    return UnitOfMeasure::query()->create([
        'name' => 'Piece',
        'abbreviation' => 'pc',
        'is_active' => true,
    ]);
}

function invoiceMaintenanceCustomer(User $user): Customer
{
    static $counter = 0;
    $counter++;

    return Customer::query()->create([
        'customer_code' => sprintf('CUST-MAIN-%04d', $counter),
        'full_name' => sprintf('Customer %d', $counter),
        'phone' => sprintf('+256720000%04d', $counter),
        'email' => sprintf('invoice-maintenance-%d@example.test', $counter),
        'created_by' => $user->id,
    ]);
}

function invoiceMaintenanceProduct(UnitOfMeasure $unit, array $overrides = []): InventoryItem
{
    static $counter = 0;
    $counter++;

    return InventoryItem::query()->create([
        'name' => sprintf('Invoice InventoryItem %d', $counter),
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

function invoiceMaintenanceInvoice(Customer $customer, Currency $currency, User $user, array $overrides = []): Invoice
{
    static $counter = 0;
    $counter++;

    return Invoice::query()->create(array_merge([
        'invoice_number' => sprintf('INV-MAIN-%04d', $counter),
        'customer_id' => $customer->id,
        'currency_id' => $currency->id,
        'invoice_date' => now()->toDateString(),
        'due_date' => now()->addDays(7)->toDateString(),
        'status' => 'issued',
        'subtotal_amount' => 100,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'total_amount' => 100,
        'amount_paid' => 0,
        'balance_due' => 100,
        'issued_at' => now(),
        'created_by' => $user->id,
    ], $overrides));
}
