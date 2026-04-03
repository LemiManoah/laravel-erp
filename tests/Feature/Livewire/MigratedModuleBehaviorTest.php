<?php

declare(strict_types=1);

use App\Enums\ProductItemType;
use App\Livewire\Currencies\IndexPage as CurrencyIndexPage;
use App\Livewire\Measurements\CreatePage as MeasurementCreatePage;
use App\Livewire\Payments\ShowPage as PaymentShowPage;
use App\Livewire\PaymentMethods\IndexPage as PaymentMethodIndexPage;
use App\Livewire\ProductCategories\IndexPage as ProductCategoryIndexPage;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Measurement;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Receipt;
use App\Models\UnitOfMeasure;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    moduleBehaviorPermissions($this->user, [
        'currencies.view',
        'currencies.delete',
        'payment-methods.view',
        'payment-methods.delete',
        'products.view',
        'products.create',
        'products.update',
        'measurements.create',
        'payments.view',
        'payments.void',
    ]);

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
});

it('does not allow deleting the default currency from the livewire index', function () {
    Livewire::test(CurrencyIndexPage::class)
        ->call('delete', $this->currency->id)
        ->assertHasErrors(['currency']);

    $this->assertDatabaseHas('currencies', [
        'id' => $this->currency->id,
    ]);
});

it('does not allow deleting a payment method that is used in a payment', function () {
    $payment = createModuleBehaviorPayment($this->user, $this->currency);

    Livewire::test(PaymentMethodIndexPage::class)
        ->call('delete', $payment->payment_method_id)
        ->assertHasErrors(['payment_method']);

    $this->assertDatabaseHas('payment_methods', [
        'id' => $payment->payment_method_id,
    ]);
});

it('does not allow deleting a product category with products attached', function () {
    $category = ProductCategory::query()->create([
        'name' => 'Tailoring',
        'description' => 'Apparel products',
        'is_active' => true,
    ]);

    createModuleBehaviorProduct($category);

    Livewire::test(ProductCategoryIndexPage::class)
        ->call('delete', $category->id);

    $this->assertDatabaseHas('product_categories', [
        'id' => $category->id,
    ]);
});

it('marks a newly created measurement as the only current one for the customer', function () {
    $customer = createModuleBehaviorCustomer($this->user);

    Measurement::query()->create([
        'customer_id' => $customer->id,
        'measurement_date' => now()->subDay()->toDateString(),
        'chest' => 38,
        'waist' => 31,
        'is_current' => true,
        'measured_by' => $this->user->id,
    ]);

    Livewire::test(MeasurementCreatePage::class, ['customer' => $customer])
        ->set('measurement_date', now()->toDateString())
        ->set('chest', '40')
        ->set('waist', '32')
        ->set('is_current', true)
        ->call('save');

    expect($customer->measurements()->where('is_current', true)->count())->toBe(1);
    $this->assertDatabaseHas('measurements', [
        'customer_id' => $customer->id,
        'chest' => 40,
        'waist' => 32,
        'is_current' => true,
    ]);
});

it('voids a payment from the livewire payment page', function () {
    $payment = createModuleBehaviorPayment($this->user, $this->currency);

    Livewire::test(PaymentShowPage::class, ['payment' => $payment])
        ->set('showVoidForm', true)
        ->set('void_reason', 'Recorded in error')
        ->call('voidPayment');

    $this->assertDatabaseHas('payments', [
        'id' => $payment->id,
        'status' => 'voided',
        'void_reason' => 'Recorded in error',
    ]);
});

function moduleBehaviorPermissions(User $user, array $permissions): void
{
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    foreach ($permissions as $permission) {
        Permission::query()->firstOrCreate([
            'name' => $permission,
            'guard_name' => 'web',
        ]);
    }

    $user->givePermissionTo($permissions);
}

function createModuleBehaviorCustomer(User $user): Customer
{
    static $counter = 0;
    $counter++;

    return Customer::query()->create([
        'customer_code' => sprintf('CUST-MB-%04d', $counter),
        'full_name' => sprintf('Behavior Customer %d', $counter),
        'phone' => sprintf('+25671111%04d', $counter),
        'email' => sprintf('behavior%d@example.test', $counter),
        'created_by' => $user->id,
    ]);
}

function createModuleBehaviorPayment(User $user, Currency $currency): Payment
{
    static $counter = 0;
    $counter++;

    $customer = createModuleBehaviorCustomer($user);
    $invoice = Invoice::query()->create([
        'invoice_number' => sprintf('INV-MB-%04d', $counter),
        'customer_id' => $customer->id,
        'currency_id' => $currency->id,
        'invoice_date' => now()->toDateString(),
        'due_date' => now()->addDays(7)->toDateString(),
        'status' => 'partially_paid',
        'subtotal_amount' => 100,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'total_amount' => 100,
        'amount_paid' => 50,
        'balance_due' => 50,
        'created_by' => $user->id,
    ]);

    $paymentMethod = PaymentMethod::query()->create([
        'name' => sprintf('Cash %d', $counter),
        'slug' => sprintf('cash-%d', $counter),
        'is_active' => true,
        'sort_order' => $counter,
    ]);

    $payment = Payment::query()->create([
        'invoice_id' => $invoice->id,
        'currency_id' => $currency->id,
        'payment_date' => now()->toDateString(),
        'amount' => 50,
        'payment_method_id' => $paymentMethod->id,
        'payment_method' => $paymentMethod->name,
        'reference_number' => sprintf('PAY-MB-%04d', $counter),
        'status' => 'valid',
        'received_by' => $user->id,
    ]);

    Receipt::query()->create([
        'receipt_number' => sprintf('RCT-MB-%04d', $counter),
        'payment_id' => $payment->id,
        'issued_date' => now()->toDateString(),
    ]);

    return $payment->refresh();
}

function createModuleBehaviorProduct(ProductCategory $category): Product
{
    static $counter = 0;
    $counter++;

    $unit = UnitOfMeasure::query()->firstOrCreate(
        ['abbreviation' => 'pc'],
        [
            'name' => 'Piece',
            'is_active' => true,
        ],
    );

    return Product::query()->create([
        'name' => sprintf('Behavior Product %d', $counter),
        'product_category_id' => $category->id,
        'item_type' => ProductItemType::StockItem,
        'tracks_inventory' => true,
        'is_sellable' => true,
        'is_purchasable' => true,
        'base_unit_id' => $unit->id,
        'allow_negative_stock' => false,
        'has_expiry' => false,
        'is_serialized' => false,
        'has_variants' => false,
        'is_active' => true,
    ]);
}
