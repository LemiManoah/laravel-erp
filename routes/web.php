<?php

use App\Http\Controllers\CentralAdmin\Auth\LoginController as CentralAdminLoginController;
use App\Http\Controllers\CentralAdmin\DashboardController as CentralAdminDashboardController;
use App\Http\Controllers\CentralAdmin\TenantController as CentralAdminTenantController;
use Illuminate\Support\Facades\Route;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->middleware('web')->group(function () {
        Route::get('/', function () {
            return auth()->check() && auth()->user()?->canAccessPlatformTenants()
                ? redirect()->route('central.admin.dashboard')
                : redirect()->route('central.login');
        })->name('home');

        Route::middleware('guest')->group(function () {
            Route::get('/admin/login', [CentralAdminLoginController::class, 'create'])->name('central.login');
            Route::post('/admin/login', [CentralAdminLoginController::class, 'store'])->name('central.login.store');
        });

        Route::middleware('central_admin')->prefix('admin')->name('central.admin.')->group(function () {
            Route::get('/', [CentralAdminDashboardController::class, 'index'])->name('dashboard');
            Route::get('/tenants', [CentralAdminTenantController::class, 'index'])->name('tenants.index');
            Route::get('/tenants/create', [CentralAdminTenantController::class, 'create'])->name('tenants.create');
            Route::post('/tenants', [CentralAdminTenantController::class, 'store'])->name('tenants.store');
            Route::get('/tenants/{tenant}/edit', [CentralAdminTenantController::class, 'edit'])->name('tenants.edit');
            Route::put('/tenants/{tenant}', [CentralAdminTenantController::class, 'update'])->name('tenants.update');
            Route::post('/tenants/{tenant}/suspend', [CentralAdminTenantController::class, 'suspend'])->name('tenants.suspend');
            Route::post('/tenants/{tenant}/reactivate', [CentralAdminTenantController::class, 'reactivate'])->name('tenants.reactivate');
            Route::post('/tenants/{tenant}/maintenance/bootstrap', [CentralAdminTenantController::class, 'rerunBootstrap'])->name('tenants.maintenance.bootstrap');
            Route::post('/tenants/{tenant}/maintenance/demo-refresh', [CentralAdminTenantController::class, 'refreshDemoData'])->name('tenants.maintenance.demo-refresh');
        });

        Route::post('/admin/logout', [CentralAdminLoginController::class, 'destroy'])
            ->middleware('central_admin')
            ->name('central.logout');
    });
}
