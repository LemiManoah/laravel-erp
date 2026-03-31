<?php

declare(strict_types=1);

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MeasurementController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Settings;
use App\Http\Controllers\UserController;
use App\Livewire\Inventory\Units\CreatePage;
use App\Livewire\Inventory\Units\EditPage;
use App\Livewire\Inventory\Units\IndexPage;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
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

    Route::get('dashboard', [DashboardController::class, 'index'])
        ->middleware(['auth', 'verified'])
        ->name('dashboard');

    Route::middleware(['auth'])->group(function () {
        Route::resource('customers', CustomerController::class)->except(['destroy']);
        Route::resource('customers.measurements', MeasurementController::class)->shallow();
        Route::resource('orders', OrderController::class);

        Route::resource('invoices', InvoiceController::class)->except(['destroy']);
        Route::post('invoices/{invoice}/issue', [InvoiceController::class, 'issue'])->name('invoices.issue');
        Route::post('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
        Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');

        Route::post('invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
        Route::post('payments/{payment}/void', [PaymentController::class, 'void'])->name('payments.void');
        Route::get('receipts/{receipt}', [ReceiptController::class, 'show'])->name('receipts.show');
        Route::get('receipts/{receipt}/print', [ReceiptController::class, 'print'])->name('receipts.print');
        Route::resource('payment-methods', PaymentMethodController::class)
            ->parameters(['payment-methods' => 'paymentMethod'])
            ->except(['show']);
        Route::resource('currencies', CurrencyController::class)->except(['show']);
        Route::post('currencies/{currency}/default', [CurrencyController::class, 'setDefault'])->name('currencies.default');

        Route::resource('products', ProductController::class)->except(['show']);
        Route::resource('product-categories', ProductCategoryController::class)->except(['show']);

        Route::resource('expenses', ExpenseController::class)->except(['destroy']);
        Route::post('expenses/{expense}/void', [ExpenseController::class, 'void'])->name('expenses.void');
        Route::resource('expense-categories', ExpenseCategoryController::class)->except(['show']);

        // Inventory Module
        Route::group([], function () {
            // Units of Measure
            Route::get('inventory/units-of-measure', IndexPage::class)
                ->name('inventory.units-of-measure.index')
                ->middleware('permission:units-of-measure.view');
            Route::get('inventory/units-of-measure/create', CreatePage::class)
                ->name('inventory.units-of-measure.create')
                ->middleware('permission:units-of-measure.create');
            Route::get('inventory/units-of-measure/{unit}/edit', EditPage::class)
                ->name('inventory.units-of-measure.edit')
                ->middleware('permission:units-of-measure.update');

            // Stock Locations
            Route::get('inventory/stock-locations', App\Livewire\Inventory\Locations\IndexPage::class)
                ->name('inventory.stock-locations.index')
                ->middleware('permission:stock-locations.view');
            Route::get('inventory/stock-locations/create', App\Livewire\Inventory\Locations\CreatePage::class)
                ->name('inventory.stock-locations.create')
                ->middleware('permission:stock-locations.create');
            Route::get('inventory/stock-locations/{location}/edit', App\Livewire\Inventory\Locations\EditPage::class)
                ->name('inventory.stock-locations.edit')
                ->middleware('permission:stock-locations.update');
        });

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('reports/sales/print', [ReportController::class, 'salesPrint'])->name('reports.sales.print');
        Route::get('reports/payments', [ReportController::class, 'payments'])->name('reports.payments');
        Route::get('reports/payments/print', [ReportController::class, 'paymentsPrint'])->name('reports.payments.print');
        Route::get('reports/expenses', [ReportController::class, 'expenses'])->name('reports.expenses');
        Route::get('reports/expenses/print', [ReportController::class, 'expensesPrint'])->name('reports.expenses.print');
        Route::get('reports/outstanding-balances', [ReportController::class, 'outstandingBalances'])->name('reports.outstanding-balances');
        Route::get('reports/outstanding-balances/print', [ReportController::class, 'outstandingBalancesPrint'])->name('reports.outstanding-balances.print');
        Route::get('reports/customer-statement', [ReportController::class, 'customerStatement'])->name('reports.customer-statement');
        Route::get('reports/customer-statement/print', [ReportController::class, 'customerStatementPrint'])->name('reports.customer-statement.print');
        Route::get('reports/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.profit-loss');
        Route::get('reports/profit-loss/print', [ReportController::class, 'profitLossPrint'])->name('reports.profit-loss.print');
        Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::resource('roles', RoleController::class)->except(['show']);
        Route::resource('users', UserController::class)->except(['show', 'destroy']);

        Route::get('settings/profile', [Settings\ProfileController::class, 'edit'])->name('settings.profile.edit');
        Route::put('settings/profile', [Settings\ProfileController::class, 'update'])->name('settings.profile.update');
        Route::delete('settings/profile', [Settings\ProfileController::class, 'destroy'])->name('settings.profile.destroy');
        Route::get('settings/password', [Settings\PasswordController::class, 'edit'])->name('settings.password.edit');
        Route::put('settings/password', [Settings\PasswordController::class, 'update'])->name('settings.password.update');
        Route::get('settings/appearance', [Settings\AppearanceController::class, 'edit'])->name('settings.appearance.edit');
        Route::put('settings/appearance', [Settings\AppearanceController::class, 'update'])->name('settings.appearance.update');
    });

    require __DIR__.'/auth.php';
});
