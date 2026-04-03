<?php

declare(strict_types=1);

use App\Livewire\Currencies\CreatePage as CurrenciesCreatePage;
use App\Livewire\Currencies\EditPage as CurrenciesEditPage;
use App\Livewire\Customers\CreatePage as CustomersCreatePage;
use App\Livewire\Customers\EditPage as CustomersEditPage;
use App\Livewire\Customers\ShowPage as CustomersShowPage;
use App\Livewire\Dashboard;
use App\Livewire\ExpenseCategories\CreatePage as ExpenseCategoriesCreatePage;
use App\Livewire\ExpenseCategories\EditPage as ExpenseCategoriesEditPage;
use App\Livewire\Expenses\CreatePage as ExpensesCreatePage;
use App\Livewire\Expenses\EditPage as ExpensesEditPage;
use App\Livewire\Expenses\ShowPage as ExpensesShowPage;
use App\Livewire\Invoices\CreatePage as InvoicesCreatePage;
use App\Livewire\Invoices\EditPage as InvoicesEditPage;
use App\Livewire\Invoices\ShowPage as InvoicesShowPage;
use App\Livewire\Inventory\Adjustments\CreatePage as InventoryAdjustmentsCreatePage;
use App\Livewire\Inventory\Locations\CreatePage as InventoryLocationsCreatePage;
use App\Livewire\Inventory\Locations\EditPage as InventoryLocationsEditPage;
use App\Livewire\Inventory\Locations\IndexPage as InventoryLocationsIndexPage;
use App\Livewire\Inventory\Monitoring\IndexPage as InventoryMonitoringIndexPage;
use App\Livewire\Inventory\Movements\CreatePage as InventoryMovementsCreatePage;
use App\Livewire\Inventory\Movements\IndexPage as InventoryMovementsIndexPage;
use App\Livewire\Inventory\Receipts\CreatePage as InventoryReceiptsCreatePage;
use App\Livewire\Inventory\Stocks\IndexPage as InventoryStocksIndexPage;
use App\Livewire\Inventory\Transfers\CreatePage as InventoryTransfersCreatePage;
use App\Livewire\Inventory\Units\CreatePage as InventoryUnitsCreatePage;
use App\Livewire\Inventory\Units\EditPage as InventoryUnitsEditPage;
use App\Livewire\Inventory\Units\IndexPage as InventoryUnitsIndexPage;
use App\Livewire\Measurements\CreatePage as MeasurementsCreatePage;
use App\Livewire\Measurements\EditPage as MeasurementsEditPage;
use App\Livewire\Measurements\IndexPage as MeasurementsIndexPage;
use App\Livewire\Measurements\ShowPage as MeasurementsShowPage;
use App\Livewire\Orders\CreatePage as OrdersCreatePage;
use App\Livewire\Orders\EditPage as OrdersEditPage;
use App\Livewire\Orders\ShowPage as OrdersShowPage;
use App\Livewire\PaymentMethods\CreatePage as PaymentMethodsCreatePage;
use App\Livewire\PaymentMethods\EditPage as PaymentMethodsEditPage;
use App\Livewire\Payments\IndexPage as PaymentsIndexPage;
use App\Livewire\Payments\ShowPage as PaymentsShowPage;
use App\Livewire\ItemCategories\CreatePage as ItemCategoriesCreatePage;
use App\Livewire\ItemCategories\EditPage as ItemCategoriesEditPage;
use App\Livewire\ItemCategories\IndexPage as ItemCategoriesIndexPage;
use App\Livewire\Purchasing\Orders\CreatePage as PurchaseOrdersCreatePage;
use App\Livewire\Purchasing\Orders\IndexPage as PurchaseOrdersIndexPage;
use App\Livewire\Purchasing\Orders\ShowPage as PurchaseOrdersShowPage;
use App\Livewire\Purchasing\Receipts\CreatePage as PurchaseReceiptsCreatePage;
use App\Livewire\Purchasing\Receipts\IndexPage as PurchaseReceiptsIndexPage;
use App\Livewire\Purchasing\Receipts\ShowPage as PurchaseReceiptsShowPage;
use App\Livewire\Purchasing\Returns\CreatePage as PurchaseReturnsCreatePage;
use App\Livewire\Purchasing\Returns\IndexPage as PurchaseReturnsIndexPage;
use App\Livewire\Purchasing\Returns\ShowPage as PurchaseReturnsShowPage;
use App\Livewire\Receipts\ShowPage as ReceiptsShowPage;
use App\Livewire\Reports\CustomerStatementPage as ReportsCustomerStatementPage;
use App\Livewire\Reports\ExpensesPage as ReportsExpensesPage;
use App\Livewire\Reports\IndexPage as ReportsIndexPage;
use App\Livewire\Reports\InventoryStatusPage as ReportsInventoryStatusPage;
use App\Livewire\Reports\OutstandingBalancesPage as ReportsOutstandingBalancesPage;
use App\Livewire\Reports\PaymentsPage as ReportsPaymentsPage;
use App\Livewire\Reports\ProfitLossPage as ReportsProfitLossPage;
use App\Livewire\Reports\SalesPage as ReportsSalesPage;
use App\Livewire\Reports\StockCardPage as ReportsStockCardPage;
use App\Livewire\Reports\SupplierPurchasingPage as ReportsSupplierPurchasingPage;
use App\Livewire\Roles\CreatePage as RolesCreatePage;
use App\Livewire\Roles\EditPage as RolesEditPage;
use App\Livewire\Settings\AppearancePage;
use App\Livewire\Settings\PasswordPage;
use App\Livewire\Settings\ProfilePage;
use App\Livewire\Suppliers\CreatePage as SuppliersCreatePage;
use App\Livewire\Suppliers\EditPage as SuppliersEditPage;
use App\Livewire\Suppliers\IndexPage as SuppliersIndexPage;
use App\Livewire\Users\CreatePage as UsersCreatePage;
use App\Livewire\Users\EditPage as UsersEditPage;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\Settings\AppearanceController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', function () {
        return auth()->check()
            ? redirect()->route('dashboard')
            : redirect()->route('login');
    })->name('tenant.home');

    Route::get('dashboard', Dashboard::class)
        ->middleware(['auth', 'verified'])
        ->name('dashboard');

    Route::middleware(['auth'])->group(function () {

        // Customers
        Route::get('customers', \App\Livewire\Customers\IndexPage::class)
            ->name('customers.index')
            ->middleware('permission:customers.view');
        Route::get('customers/create', CustomersCreatePage::class)
            ->name('customers.create')
            ->middleware('permission:customers.create');
        Route::get('customers/{customer}', CustomersShowPage::class)
            ->name('customers.show')
            ->middleware('permission:customers.view');
        Route::get('customers/{customer}/edit', CustomersEditPage::class)
            ->name('customers.edit')
            ->middleware('permission:customers.update');
        Route::get('customers/{customer}/measurements', MeasurementsIndexPage::class)
            ->name('customers.measurements.index')
            ->middleware('permission:measurements.view');
        Route::get('customers/{customer}/measurements/create', MeasurementsCreatePage::class)
            ->name('customers.measurements.create')
            ->middleware('permission:measurements.create');
        Route::get('measurements/{measurement}', MeasurementsShowPage::class)
            ->name('measurements.show')
            ->middleware('permission:measurements.view');
        Route::get('measurements/{measurement}/edit', MeasurementsEditPage::class)
            ->name('measurements.edit')
            ->middleware('permission:measurements.update');

        // Orders
        Route::get('orders', \App\Livewire\Orders\IndexPage::class)
            ->name('orders.index')
            ->middleware('permission:orders.view');
        Route::get('orders/create', OrdersCreatePage::class)
            ->name('orders.create')
            ->middleware('permission:orders.create');
        Route::get('orders/{order}', OrdersShowPage::class)
            ->name('orders.show')
            ->middleware('permission:orders.view');
        Route::get('orders/{order}/edit', OrdersEditPage::class)
            ->name('orders.edit')
            ->middleware('permission:orders.update');

        // Invoices
        Route::get('invoices', \App\Livewire\Invoices\IndexPage::class)
            ->name('invoices.index')
            ->middleware('permission:invoices.view');
        Route::get('invoices/create', InvoicesCreatePage::class)
            ->name('invoices.create')
            ->middleware('permission:invoices.create');
        Route::get('invoices/{invoice}', InvoicesShowPage::class)
            ->name('invoices.show')
            ->middleware('permission:invoices.view');
        Route::get('invoices/{invoice}/edit', InvoicesEditPage::class)
            ->name('invoices.edit')
            ->middleware('permission:invoices.update');
        Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');

        // Payments & Receipts
        Route::get('payments', PaymentsIndexPage::class)
            ->name('payments.index')
            ->middleware('permission:payments.view');
        Route::get('payments/{payment}', PaymentsShowPage::class)
            ->name('payments.show')
            ->middleware('permission:payments.view');
        Route::get('receipts/{receipt}', ReceiptsShowPage::class)->name('receipts.show');
        Route::get('receipts/{receipt}/print', [ReceiptController::class, 'print'])->name('receipts.print');

        // Payment Methods
        Route::get('payment-methods', \App\Livewire\PaymentMethods\IndexPage::class)
            ->name('payment-methods.index')
            ->middleware('permission:payment-methods.view');
        Route::get('payment-methods/create', PaymentMethodsCreatePage::class)
            ->name('payment-methods.create')
            ->middleware('permission:payment-methods.create');
        Route::get('payment-methods/{paymentMethod}/edit', PaymentMethodsEditPage::class)
            ->name('payment-methods.edit')
            ->middleware('permission:payment-methods.update');

        // Currencies
        Route::get('currencies', \App\Livewire\Currencies\IndexPage::class)
            ->name('currencies.index')
            ->middleware('permission:currencies.view');
        Route::get('currencies/create', CurrenciesCreatePage::class)
            ->name('currencies.create')
            ->middleware('permission:currencies.create');
        Route::get('currencies/{currency}/edit', CurrenciesEditPage::class)
            ->name('currencies.edit')
            ->middleware('permission:currencies.update');
        Route::post('currencies/{currency}/default', [CurrencyController::class, 'setDefault'])->name('currencies.default');

        // Inventory Items
        Route::get('inventory-items', \App\Livewire\InventoryItems\IndexPage::class)
            ->name('inventory-items.index')
            ->middleware('permission:inventory-items.view');
        Route::get('inventory-items/create', \App\Livewire\InventoryItems\CreatePage::class)
            ->name('inventory-items.create')
            ->middleware('permission:inventory-items.create');
        Route::get('inventory-items/{inventoryItem}/edit', \App\Livewire\InventoryItems\EditPage::class)
            ->name('inventory-items.edit')
            ->middleware('permission:inventory-items.update');
        Route::get('item-categories', ItemCategoriesIndexPage::class)
            ->name('item-categories.index')
            ->middleware('permission:inventory-items.view');
        Route::get('item-categories/create', ItemCategoriesCreatePage::class)
            ->name('item-categories.create')
            ->middleware('permission:inventory-items.create');
        Route::get('item-categories/{itemCategory}/edit', ItemCategoriesEditPage::class)
            ->name('item-categories.edit')
            ->middleware('permission:inventory-items.update');

        // Expenses
        Route::get('expenses', \App\Livewire\Expenses\IndexPage::class)
            ->name('expenses.index')
            ->middleware('permission:expenses.view');
        Route::get('expenses/create', ExpensesCreatePage::class)
            ->name('expenses.create')
            ->middleware('permission:expenses.create');
        Route::get('expenses/{expense}', ExpensesShowPage::class)
            ->name('expenses.show')
            ->middleware('permission:expenses.view');
        Route::get('expenses/{expense}/edit', ExpensesEditPage::class)
            ->name('expenses.edit')
            ->middleware('permission:expenses.update');

        // Expense Categories
        Route::get('expense-categories', \App\Livewire\ExpenseCategories\IndexPage::class)
            ->name('expense-categories.index')
            ->middleware('permission:expenses.view');
        Route::get('expense-categories/create', ExpenseCategoriesCreatePage::class)
            ->name('expense-categories.create')
            ->middleware('permission:expenses.create');
        Route::get('expense-categories/{expenseCategory}/edit', ExpenseCategoriesEditPage::class)
            ->name('expense-categories.edit')
            ->middleware('permission:expenses.update');

        // Suppliers
        Route::get('suppliers', SuppliersIndexPage::class)
            ->name('suppliers.index')
            ->middleware('permission:suppliers.view');
        Route::get('suppliers/create', SuppliersCreatePage::class)
            ->name('suppliers.create')
            ->middleware('permission:suppliers.create');
        Route::get('suppliers/{supplier}/edit', SuppliersEditPage::class)
            ->name('suppliers.edit')
            ->middleware('permission:suppliers.update');

        // Purchase Orders
        Route::get('purchase-receipts', PurchaseReceiptsIndexPage::class)
            ->name('purchase-receipts.index')
            ->middleware('permission:purchase-receipts.view');
        Route::get('purchase-receipts/create', PurchaseReceiptsCreatePage::class)
            ->name('purchase-receipts.create')
            ->middleware('permission:purchase-receipts.create');
        Route::get('purchase-receipts/{receipt}', PurchaseReceiptsShowPage::class)
            ->name('purchase-receipts.show')
            ->middleware('permission:purchase-receipts.view');
        Route::get('purchase-orders', PurchaseOrdersIndexPage::class)
            ->name('purchase-orders.index')
            ->middleware('permission:purchase-orders.view');
        Route::get('purchase-orders/create', PurchaseOrdersCreatePage::class)
            ->name('purchase-orders.create')
            ->middleware('permission:purchase-orders.create');
        Route::get('purchase-orders/{order}', PurchaseOrdersShowPage::class)
            ->name('purchase-orders.show')
            ->middleware('permission:purchase-orders.view');
        Route::get('purchase-returns', PurchaseReturnsIndexPage::class)
            ->name('purchase-returns.index')
            ->middleware('permission:purchase-returns.view');
        Route::get('purchase-returns/create', PurchaseReturnsCreatePage::class)
            ->name('purchase-returns.create')
            ->middleware('permission:purchase-returns.create');
        Route::get('purchase-returns/{purchaseReturn}', PurchaseReturnsShowPage::class)
            ->name('purchase-returns.show')
            ->middleware('permission:purchase-returns.view');

        // Inventory Module
        Route::get('inventory/units-of-measure', InventoryUnitsIndexPage::class)
            ->name('inventory.units-of-measure.index')
            ->middleware('permission:units-of-measure.view');
        Route::get('inventory/units-of-measure/create', InventoryUnitsCreatePage::class)
            ->name('inventory.units-of-measure.create')
            ->middleware('permission:units-of-measure.create');
        Route::get('inventory/units-of-measure/{unit}/edit', InventoryUnitsEditPage::class)
            ->name('inventory.units-of-measure.edit')
            ->middleware('permission:units-of-measure.update');
        Route::get('inventory/stock-locations', InventoryLocationsIndexPage::class)
            ->name('inventory.stock-locations.index')
            ->middleware('permission:stock-locations.view');
        Route::get('inventory/stock-locations/create', InventoryLocationsCreatePage::class)
            ->name('inventory.stock-locations.create')
            ->middleware('permission:stock-locations.create');
        Route::get('inventory/stock-locations/{location}/edit', InventoryLocationsEditPage::class)
            ->name('inventory.stock-locations.edit')
            ->middleware('permission:stock-locations.update');
        Route::get('inventory/stocks', InventoryStocksIndexPage::class)
            ->name('inventory.stocks.index')
            ->middleware('permission:inventory-stocks.view');
        Route::get('inventory/monitoring', InventoryMonitoringIndexPage::class)
            ->name('inventory.monitoring.index')
            ->middleware('permission:inventory-stocks.view');
        Route::get('inventory/receipts/create', InventoryReceiptsCreatePage::class)
            ->name('inventory.receipts.create')
            ->middleware('permission:inventory-movements.create');
        Route::get('inventory/adjustments/create', InventoryAdjustmentsCreatePage::class)
            ->name('inventory.adjustments.create')
            ->middleware('permission:inventory-movements.create');
        Route::get('inventory/movements', InventoryMovementsIndexPage::class)
            ->name('inventory.movements.index')
            ->middleware('permission:inventory-movements.view');
        Route::get('inventory/movements/create', InventoryMovementsCreatePage::class)
            ->name('inventory.movements.create')
            ->middleware('permission:inventory-movements.create');
        Route::get('inventory/transfers/create', InventoryTransfersCreatePage::class)
            ->name('inventory.transfers.create')
            ->middleware('permission:inventory-transfers.create');

        // Reports
        Route::get('reports', ReportsIndexPage::class)
            ->name('reports.index')
            ->middleware('permission:reports.view');
        Route::get('reports/inventory-status', ReportsInventoryStatusPage::class)
            ->name('reports.inventory-status')
            ->middleware('permission:reports.view');
        Route::get('reports/inventory-status/print', ReportsInventoryStatusPage::class)
            ->name('reports.inventory-status.print')
            ->middleware('permission:reports.view');
        Route::get('reports/stock-card', ReportsStockCardPage::class)
            ->name('reports.stock-card')
            ->middleware('permission:reports.view');
        Route::get('reports/stock-card/print', ReportsStockCardPage::class)
            ->name('reports.stock-card.print')
            ->middleware('permission:reports.view');
        Route::get('reports/supplier-purchasing', ReportsSupplierPurchasingPage::class)
            ->name('reports.supplier-purchasing')
            ->middleware('permission:reports.view');
        Route::get('reports/supplier-purchasing/print', ReportsSupplierPurchasingPage::class)
            ->name('reports.supplier-purchasing.print')
            ->middleware('permission:reports.view');
        Route::get('reports/sales', ReportsSalesPage::class)
            ->name('reports.sales')
            ->middleware('permission:reports.view');
        Route::get('reports/sales/print', ReportsSalesPage::class)
            ->name('reports.sales.print')
            ->middleware('permission:reports.view');
        Route::get('reports/payments', ReportsPaymentsPage::class)
            ->name('reports.payments')
            ->middleware('permission:reports.view');
        Route::get('reports/payments/print', ReportsPaymentsPage::class)
            ->name('reports.payments.print')
            ->middleware('permission:reports.view');
        Route::get('reports/expenses', ReportsExpensesPage::class)
            ->name('reports.expenses')
            ->middleware('permission:reports.view');
        Route::get('reports/expenses/print', ReportsExpensesPage::class)
            ->name('reports.expenses.print')
            ->middleware('permission:reports.view');
        Route::get('reports/outstanding-balances', ReportsOutstandingBalancesPage::class)
            ->name('reports.outstanding-balances')
            ->middleware('permission:reports.view');
        Route::get('reports/outstanding-balances/print', ReportsOutstandingBalancesPage::class)
            ->name('reports.outstanding-balances.print')
            ->middleware('permission:reports.view');
        Route::get('reports/customer-statement', ReportsCustomerStatementPage::class)
            ->name('reports.customer-statement')
            ->middleware('permission:reports.view');
        Route::get('reports/customer-statement/print', ReportsCustomerStatementPage::class)
            ->name('reports.customer-statement.print')
            ->middleware('permission:reports.view');
        Route::get('reports/profit-loss', ReportsProfitLossPage::class)
            ->name('reports.profit-loss')
            ->middleware('permission:reports.view');
        Route::get('reports/profit-loss/print', ReportsProfitLossPage::class)
            ->name('reports.profit-loss.print')
            ->middleware('permission:reports.view');

        Route::get('activity-logs', \App\Livewire\ActivityLogs\IndexPage::class)
            ->name('activity-logs.index')
            ->middleware('permission:activity-logs.view');

        // Roles
        Route::get('roles', \App\Livewire\Roles\IndexPage::class)
            ->name('roles.index')
            ->middleware('permission:users.view');
        Route::get('roles/create', RolesCreatePage::class)
            ->name('roles.create')
            ->middleware('permission:users.create');
        Route::get('roles/{role}/edit', RolesEditPage::class)
            ->name('roles.edit')
            ->middleware('permission:users.update');

        // Users
        Route::get('users', \App\Livewire\Users\IndexPage::class)
            ->name('users.index')
            ->middleware('permission:users.view');
        Route::get('users/create', UsersCreatePage::class)
            ->name('users.create')
            ->middleware('permission:users.create');
        Route::get('users/{user}/edit', UsersEditPage::class)
            ->name('users.edit')
            ->middleware('permission:users.update');

        // Settings
        Route::get('settings/profile', ProfilePage::class)
            ->name('settings.profile.edit')
            ->middleware('permission:settings.profile.update');
        Route::put('settings/profile', [ProfileController::class, 'update'])
            ->name('settings.profile.update')
            ->middleware('permission:settings.profile.update');
        Route::delete('settings/profile', [ProfileController::class, 'destroy'])
            ->name('settings.profile.destroy');
        Route::get('settings/password', PasswordPage::class)
            ->name('settings.password.edit')
            ->middleware('permission:settings.password.update');
        Route::put('settings/password', [PasswordController::class, 'update'])
            ->name('settings.password.update')
            ->middleware('permission:settings.password.update');
        Route::get('settings/appearance', AppearancePage::class)
            ->name('settings.appearance.edit')
            ->middleware('permission:settings.appearance.update');
        Route::put('settings/appearance', [AppearanceController::class, 'update'])
            ->name('settings.appearance.update')
            ->middleware('permission:settings.appearance.update');
    });

    require __DIR__.'/auth.php';
});

