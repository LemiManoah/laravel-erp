<?php

declare(strict_types=1);

use App\Actions\Dashboard\ComputeDashboardDataAction;
use App\Actions\Report\ComputeInventoryStatusReportAction;
use App\Actions\Report\ComputeProfitLossReportAction;
use App\Actions\Report\ComputeSalesReportAction;
use App\Enums\ProductItemType;
use App\Enums\StockLocationType;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\InventoryStock;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\StockLocation;
use App\Models\UnitOfMeasure;
use App\Models\User;

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

    $this->paymentMethod = PaymentMethod::query()->create([
        'name' => 'Cash',
        'slug' => 'cash',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->expenseCategory = ExpenseCategory::query()->create([
        'name' => 'Operations',
        'description' => 'Operational spend',
        'is_active' => true,
    ]);

    $this->unit = reportingWorkflowUnit();
    $this->location = StockLocation::query()->create([
        'name' => 'Warehouse',
        'code' => 'RPT',
        'location_type' => StockLocationType::Warehouse,
        'is_default' => true,
        'is_active' => true,
    ]);
});

it('computes sales report totals excluding draft and cancelled invoices', function (): void {
    $customer = reportingWorkflowCustomer($this->user);

    reportingWorkflowInvoice($customer, $this->currency, $this->user, [
        'invoice_number' => 'INV-RPT-0001',
        'invoice_date' => '2026-04-02',
        'status' => 'issued',
        'total_amount' => 120,
        'amount_paid' => 20,
        'balance_due' => 100,
    ]);

    reportingWorkflowInvoice($customer, $this->currency, $this->user, [
        'invoice_number' => 'INV-RPT-0002',
        'invoice_date' => '2026-04-03',
        'status' => 'paid',
        'total_amount' => 80,
        'amount_paid' => 80,
        'balance_due' => 0,
    ]);

    reportingWorkflowInvoice($customer, $this->currency, $this->user, [
        'invoice_number' => 'INV-RPT-DRAFT',
        'invoice_date' => '2026-04-03',
        'status' => 'draft',
        'total_amount' => 999,
        'amount_paid' => 0,
        'balance_due' => 999,
        'issued_at' => null,
    ]);

    reportingWorkflowInvoice($customer, $this->currency, $this->user, [
        'invoice_number' => 'INV-RPT-CANCELLED',
        'invoice_date' => '2026-04-03',
        'status' => 'cancelled',
        'total_amount' => 500,
        'amount_paid' => 0,
        'balance_due' => 0,
    ]);

    $report = app(ComputeSalesReportAction::class)->handle('2026-04-01', '2026-04-03');

    expect($report['summary']['invoice_count'])->toBe(2);
    expect($report['summary']['total_invoiced'])->toBe(200.0);
    expect($report['summary']['total_paid'])->toBe(100.0);
    expect($report['summary']['total_balance'])->toBe(100.0);
});

it('computes inventory status counts for low stock and expiry buckets', function (): void {
    $lowStockProduct = reportingWorkflowProduct($this->unit, [
        'name' => 'Low Stock Product',
        'reorder_level' => 5,
    ]);

    $healthyProduct = reportingWorkflowProduct($this->unit, [
        'name' => 'Healthy Product',
        'reorder_level' => 2,
    ]);

    InventoryStock::query()->create([
        'product_id' => $lowStockProduct->id,
        'location_id' => $this->location->id,
        'quantity_on_hand' => 3,
        'received_at' => now()->subDay()->toDateString(),
    ]);

    InventoryStock::query()->create([
        'product_id' => $healthyProduct->id,
        'location_id' => $this->location->id,
        'quantity_on_hand' => 9,
        'received_at' => now()->subDay()->toDateString(),
    ]);

    InventoryStock::query()->create([
        'product_id' => $healthyProduct->id,
        'location_id' => $this->location->id,
        'batch_number' => 'BATCH-NEAR',
        'expiry_date' => now()->addDays(5)->toDateString(),
        'quantity_on_hand' => 2,
        'received_at' => now()->subDay()->toDateString(),
    ]);

    InventoryStock::query()->create([
        'product_id' => $healthyProduct->id,
        'location_id' => $this->location->id,
        'batch_number' => 'BATCH-EXPIRED',
        'expiry_date' => now()->subDay()->toDateString(),
        'quantity_on_hand' => 1,
        'received_at' => now()->subWeek()->toDateString(),
    ]);

    $report = app(ComputeInventoryStatusReportAction::class)->handle();

    expect($report['summary']['tracked_products'])->toBe(2);
    expect($report['summary']['low_stock_products'])->toBe(1);
    expect($report['summary']['near_expiry_rows'])->toBe(1);
    expect($report['summary']['expired_rows'])->toBe(1);
    expect($report['stock_by_location'])->toHaveCount(1);
});

it('computes profit and loss using only valid payments and expenses within the date range', function (): void {
    $customer = reportingWorkflowCustomer($this->user);
    $invoice = reportingWorkflowInvoice($customer, $this->currency, $this->user, [
        'invoice_number' => 'INV-PNL-0001',
        'invoice_date' => '2026-04-03',
        'status' => 'paid',
        'total_amount' => 100,
        'amount_paid' => 100,
        'balance_due' => 0,
    ]);

    Payment::query()->create([
        'invoice_id' => $invoice->id,
        'currency_id' => $this->currency->id,
        'payment_date' => '2026-04-03',
        'amount' => 100,
        'payment_method_id' => $this->paymentMethod->id,
        'payment_method' => $this->paymentMethod->name,
        'reference_number' => 'PAY-PNL-0001',
        'status' => 'valid',
        'received_by' => $this->user->id,
    ]);

    Payment::query()->create([
        'invoice_id' => $invoice->id,
        'currency_id' => $this->currency->id,
        'payment_date' => '2026-04-03',
        'amount' => 500,
        'payment_method_id' => $this->paymentMethod->id,
        'payment_method' => $this->paymentMethod->name,
        'reference_number' => 'PAY-PNL-VOID',
        'status' => 'voided',
        'received_by' => $this->user->id,
    ]);

    Expense::query()->create([
        'expense_category_id' => $this->expenseCategory->id,
        'currency_id' => $this->currency->id,
        'payment_method_id' => $this->paymentMethod->id,
        'payment_method' => $this->paymentMethod->name,
        'expense_date' => '2026-04-03',
        'amount' => 30,
        'vendor_name' => 'Tailor Supplies',
        'reference_number' => 'EXP-PNL-0001',
        'description' => 'Needles and buttons',
        'status' => 'valid',
        'created_by' => $this->user->id,
    ]);

    Expense::query()->create([
        'expense_category_id' => $this->expenseCategory->id,
        'currency_id' => $this->currency->id,
        'payment_method_id' => $this->paymentMethod->id,
        'payment_method' => $this->paymentMethod->name,
        'expense_date' => '2026-04-03',
        'amount' => 70,
        'vendor_name' => 'Ignored Vendor',
        'reference_number' => 'EXP-PNL-VOID',
        'description' => 'Voided expense',
        'status' => 'voided',
        'created_by' => $this->user->id,
    ]);

    $report = app(ComputeProfitLossReportAction::class)->handle('2026-04-01', '2026-04-03');

    expect($report['revenue'])->toBe(100.0);
    expect($report['total_expenses'])->toBe(30.0);
});

it('computes dashboard headline stats and recent activity lists', function (): void {
    $newCustomer = reportingWorkflowCustomer($this->user, [
        'customer_code' => 'CUST-TODAY-0001',
        'email' => 'dashboard-today@example.test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $olderCustomer = reportingWorkflowCustomer($this->user, [
        'customer_code' => 'CUST-OLD-0001',
        'email' => 'dashboard-old@example.test',
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay(),
    ]);

    $todayInvoice = reportingWorkflowInvoice($newCustomer, $this->currency, $this->user, [
        'invoice_number' => 'INV-DASH-0001',
        'invoice_date' => now()->toDateString(),
        'issued_at' => now(),
        'status' => 'issued',
        'total_amount' => 120,
        'amount_paid' => 20,
        'balance_due' => 100,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    reportingWorkflowInvoice($olderCustomer, $this->currency, $this->user, [
        'invoice_number' => 'INV-DASH-OLD',
        'invoice_date' => now()->subDay()->toDateString(),
        'issued_at' => now()->subDay(),
        'status' => 'paid',
        'total_amount' => 80,
        'amount_paid' => 80,
        'balance_due' => 0,
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay(),
    ]);

    $todayPayment = Payment::query()->create([
        'invoice_id' => $todayInvoice->id,
        'currency_id' => $this->currency->id,
        'payment_date' => now()->toDateString(),
        'amount' => 20,
        'payment_method_id' => $this->paymentMethod->id,
        'payment_method' => $this->paymentMethod->name,
        'reference_number' => 'PAY-DASH-0001',
        'status' => 'valid',
        'received_by' => $this->user->id,
    ]);

    $todayPayment->forceFill([
        'created_at' => now(),
        'updated_at' => now(),
    ])->saveQuietly();

    $todayExpense = Expense::query()->create([
        'expense_category_id' => $this->expenseCategory->id,
        'currency_id' => $this->currency->id,
        'payment_method_id' => $this->paymentMethod->id,
        'payment_method' => $this->paymentMethod->name,
        'expense_date' => now()->toDateString(),
        'amount' => 15,
        'vendor_name' => 'Dashboard Vendor',
        'reference_number' => 'EXP-DASH-0001',
        'description' => 'Pins',
        'status' => 'valid',
        'created_by' => $this->user->id,
    ]);

    $todayExpense->forceFill([
        'created_at' => now(),
        'updated_at' => now(),
    ])->saveQuietly();

    $activeOrder = Order::query()->create([
        'order_number' => 'ORD-DASH-0001',
        'customer_id' => $newCustomer->id,
        'currency_id' => $this->currency->id,
        'order_date' => now()->toDateString(),
        'status' => 'confirmed',
        'priority' => 'high',
        'created_by' => $this->user->id,
    ]);

    $activeOrder->forceFill([
        'created_at' => now(),
        'updated_at' => now(),
    ])->saveQuietly();

    $readyOrder = Order::query()->create([
        'order_number' => 'ORD-DASH-0002',
        'customer_id' => $olderCustomer->id,
        'currency_id' => $this->currency->id,
        'order_date' => now()->toDateString(),
        'status' => 'ready_for_delivery',
        'priority' => 'medium',
        'created_by' => $this->user->id,
    ]);

    $readyOrder->forceFill([
        'created_at' => now()->subMinute(),
        'updated_at' => now()->subMinute(),
    ])->saveQuietly();

    reportingWorkflowInvoice($newCustomer, $this->currency, $this->user, [
        'invoice_number' => 'INV-DASH-OVERDUE',
        'invoice_date' => now()->subDays(3)->toDateString(),
        'due_date' => now()->subDay()->toDateString(),
        'issued_at' => now()->subDays(3),
        'status' => 'overdue',
        'total_amount' => 75,
        'amount_paid' => 0,
        'balance_due' => 75,
        'created_at' => now()->subDays(3),
        'updated_at' => now()->subDays(3),
    ]);

    $dashboard = app(ComputeDashboardDataAction::class)->handle();

    expect($dashboard['stats']['new_customers_today'])->toBe(1);
    expect($dashboard['stats']['invoices_issued_today'])->toBe(1);
    expect($dashboard['stats']['collected_today'])->toBe(20.0);
    expect($dashboard['stats']['expenses_today'])->toBe(15.0);
    expect($dashboard['stats']['unpaid_balances'])->toBe(175.0);
    expect($dashboard['stats']['overdue_invoices'])->toBe(1);
    expect($dashboard['stats']['active_orders'])->toBe(1);
    expect($dashboard['stats']['ready_orders'])->toBe(1);
    expect($dashboard['recent_orders'])->not->toBeEmpty();
    expect($dashboard['recent_invoices'])->not->toBeEmpty();
    expect($dashboard['recent_payments'])->not->toBeEmpty();
});

function reportingWorkflowUnit(): UnitOfMeasure
{
    return UnitOfMeasure::query()->create([
        'name' => 'Piece',
        'abbreviation' => 'pc',
        'is_active' => true,
    ]);
}

function reportingWorkflowProduct(UnitOfMeasure $unit, array $overrides = []): Product
{
    static $counter = 0;
    $counter++;

    return Product::query()->create([
        'name' => sprintf('Report Product %d', $counter),
        'item_type' => ProductItemType::StockItem,
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

function reportingWorkflowCustomer(User $user, array $overrides = []): Customer
{
    static $counter = 0;
    $counter++;

    $attributes = array_merge([
        'customer_code' => sprintf('CUST-RPT-%04d', $counter),
        'full_name' => sprintf('Report Customer %d', $counter),
        'phone' => sprintf('+256730000%04d', $counter),
        'email' => sprintf('report-customer-%d@example.test', $counter),
        'created_by' => $user->id,
    ], $overrides);

    $timestamps = array_intersect_key($attributes, array_flip(['created_at', 'updated_at']));
    unset($attributes['created_at'], $attributes['updated_at']);

    $customer = Customer::query()->create($attributes);

    if ($timestamps !== []) {
        $customer->forceFill($timestamps)->saveQuietly();
    }

    return $customer;
}

function reportingWorkflowInvoice(Customer $customer, Currency $currency, User $user, array $overrides = []): Invoice
{
    static $counter = 0;
    $counter++;

    $attributes = array_merge([
        'invoice_number' => sprintf('INV-RPT-%04d', $counter),
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
    ], $overrides);

    $timestamps = array_intersect_key($attributes, array_flip(['created_at', 'updated_at']));
    unset($attributes['created_at'], $attributes['updated_at']);

    $invoice = Invoice::query()->create($attributes);

    if ($timestamps !== []) {
        $invoice->forceFill($timestamps)->saveQuietly();
    }

    return $invoice;
}
