<?php

declare(strict_types=1);

use App\Enums\ProductItemType;
use App\Livewire\Settings\ProfilePage;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\UnitOfMeasure;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
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
});

dataset('full_page_livewire_routes', [
    ['customers.index', ['customers.view'], null],
    ['customers.create', ['customers.create'], null],
    ['customers.show', ['customers.view'], 'customer'],
    ['customers.edit', ['customers.update'], 'customer'],
    ['orders.index', ['orders.view'], null],
    ['orders.create', ['orders.create'], null],
    ['orders.show', ['orders.view'], 'order'],
    ['orders.edit', ['orders.update'], 'order'],
    ['invoices.index', ['invoices.view'], null],
    ['invoices.create', ['invoices.create'], null],
    ['invoices.show', ['invoices.view'], 'invoice'],
    ['invoices.edit', ['invoices.update'], 'invoice'],
    ['expenses.index', ['expenses.view'], null],
    ['expenses.create', ['expenses.create'], null],
    ['expenses.show', ['expenses.view'], 'expense'],
    ['expenses.edit', ['expenses.update'], 'expense'],
    ['products.index', ['products.view'], null],
    ['products.create', ['products.create'], null],
    ['products.edit', ['products.update'], 'product'],
    ['payment-methods.index', ['payment-methods.view'], null],
    ['payment-methods.create', ['payment-methods.create'], null],
    ['payment-methods.edit', ['payment-methods.update'], 'payment_method'],
    ['currencies.index', ['currencies.view'], null],
    ['currencies.create', ['currencies.create'], null],
    ['currencies.edit', ['currencies.update'], 'currency'],
    ['users.index', ['users.view'], null],
    ['users.create', ['users.create'], null],
    ['users.edit', ['users.update'], 'user'],
    ['roles.index', ['users.view'], null],
    ['roles.create', ['users.create'], null],
    ['roles.edit', ['users.update'], 'role'],
    ['activity-logs.index', ['activity-logs.view'], null],
    ['settings.profile.edit', ['settings.profile.update'], null],
    ['settings.password.edit', ['settings.password.update'], null],
    ['settings.appearance.edit', ['settings.appearance.update'], null],
]);

it('serves converted livewire full-page routes', function (string $routeName, array $permissions, ?string $parameterType) {
    grantPermissions($this->user, $permissions);

    $parameters = match ($parameterType) {
        'currency' => ['currency' => $this->currency],
        'customer' => ['customer' => createCustomerForTests($this->user)],
        'expense' => ['expense' => createExpenseForTests($this->user, $this->currency)],
        'invoice' => ['invoice' => createInvoiceForTests($this->user, $this->currency)],
        'order' => ['order' => createOrderForTests($this->user, $this->currency)],
        'payment_method' => ['paymentMethod' => createPaymentMethodForTests()],
        'product' => ['product' => createProductForTests('Edit Route Product')],
        'role' => ['role' => createRoleForTests()],
        'user' => ['user' => User::factory()->create()],
        default => [],
    };

    $this->get(route($routeName, $parameters))->assertOk();
})->with('full_page_livewire_routes');

it('validates unique profile email in the livewire settings page', function () {
    grantPermissions($this->user, ['settings.profile.update']);

    User::factory()->create([
        'email' => 'taken@example.test',
    ]);

    Livewire::test(ProfilePage::class)
        ->set('name', 'Updated User')
        ->set('email', 'taken@example.test')
        ->call('save')
        ->assertHasErrors(['email']);
});

function grantPermissions(User $user, array $permissions): void
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

function createCustomerForTests(User $user): Customer
{
    static $counter = 0;
    $counter++;

    return Customer::query()->create([
        'customer_code' => sprintf('CUST-%04d', $counter),
        'full_name' => sprintf('Customer %d', $counter),
        'phone' => sprintf('+25670000%04d', $counter),
        'email' => sprintf('customer%d@example.test', $counter),
        'created_by' => $user->id,
    ]);
}

function createPaymentMethodForTests(): PaymentMethod
{
    static $counter = 0;
    $counter++;

    return PaymentMethod::query()->create([
        'name' => sprintf('Payment Method %d', $counter),
        'slug' => sprintf('payment-method-%d', $counter),
        'is_active' => true,
        'sort_order' => $counter,
    ]);
}

function createProductForTests(string $name, array $overrides = []): Product
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
        'name' => sprintf('%s %d', $name, $counter),
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
        ...$overrides,
    ]);
}

function createOrderForTests(User $user, Currency $currency): Order
{
    static $counter = 0;
    $counter++;

    return Order::query()->create([
        'order_number' => sprintf('ORD-%04d', $counter),
        'customer_id' => createCustomerForTests($user)->id,
        'currency_id' => $currency->id,
        'order_date' => now()->toDateString(),
        'status' => 'draft',
        'priority' => 'medium',
        'created_by' => $user->id,
    ]);
}

function createInvoiceForTests(User $user, Currency $currency): Invoice
{
    static $counter = 0;
    $counter++;

    return Invoice::query()->create([
        'invoice_number' => sprintf('INV-%04d', $counter),
        'customer_id' => createCustomerForTests($user)->id,
        'currency_id' => $currency->id,
        'invoice_date' => now()->toDateString(),
        'due_date' => now()->addDays(7)->toDateString(),
        'status' => 'draft',
        'subtotal_amount' => 100,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'total_amount' => 100,
        'amount_paid' => 0,
        'balance_due' => 100,
        'created_by' => $user->id,
    ]);
}

function createExpenseForTests(User $user, Currency $currency): Expense
{
    static $counter = 0;
    $counter++;

    $category = ExpenseCategory::query()->create([
        'name' => sprintf('Expense Category %d', $counter),
        'description' => 'Expense category',
        'is_active' => true,
    ]);

    $paymentMethod = createPaymentMethodForTests();

    return Expense::query()->create([
        'expense_category_id' => $category->id,
        'currency_id' => $currency->id,
        'payment_method_id' => $paymentMethod->id,
        'payment_method' => $paymentMethod->name,
        'expense_date' => now()->toDateString(),
        'amount' => 50,
        'vendor_name' => 'Vendor',
        'reference_number' => sprintf('EXP-%04d', $counter),
        'description' => 'Expense description',
        'status' => 'recorded',
        'created_by' => $user->id,
    ]);
}

function createRoleForTests(): Role
{
    static $counter = 0;
    $counter++;

    return Role::findOrCreate(sprintf('Role %d', $counter), 'web');
}
