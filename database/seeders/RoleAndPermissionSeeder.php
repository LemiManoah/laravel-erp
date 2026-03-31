<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

final class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            'customers.view',
            'customers.create',
            'customers.update',
            'customers.delete',
            'invoices.view',
            'invoices.create',
            'invoices.update',
            'invoices.issue',
            'invoices.cancel',
            'invoices.print',
            'payments.view',
            'payments.create',
            'payments.void',
            'payment-methods.view',
            'payment-methods.create',
            'payment-methods.update',
            'payment-methods.delete',
            'currencies.view',
            'currencies.create',
            'currencies.update',
            'currencies.delete',
            'receipts.view',
            'expenses.view',
            'expenses.create',
            'expenses.update',
            'expenses.void',
            'reports.view',
            'activity-logs.view',
            'orders.view',
            'orders.create',
            'orders.update',
            'orders.delete',
            'products.view',
            'products.create',
            'products.update',
            'products.delete',
            'measurements.view',
            'measurements.create',
            'measurements.update',
            'measurements.delete',
            'users.view',
            'users.create',
            'users.update',
            'settings.profile.update',
            'settings.password.update',
            'settings.appearance.update',
            // Inventory Permissions
            'units-of-measure.view',
            'units-of-measure.create',
            'units-of-measure.update',
            'units-of-measure.delete',
            'stock-locations.view',
            'stock-locations.create',
            'stock-locations.update',
            'stock-locations.delete',
            'inventory-batches.view',
            'inventory-batches.create',
            'inventory-batches.update',
            'inventory-batches.delete',
            'inventory-movements.view',
            'inventory-movements.create',
            'inventory-movements.update',
            'inventory-movements.delete',
            'inventory-transfers.create',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $admin = Role::findOrCreate('Admin', 'web');
        $sales = Role::findOrCreate('Sales', 'web');
        $accountant = Role::findOrCreate('Accountant', 'web');
        $tailor = Role::findOrCreate('Tailor', 'web');

        $admin->syncPermissions(Permission::all());

        $sales->syncPermissions([
            'dashboard.view',
            'customers.view',
            'customers.create',
            'customers.update',
            'invoices.view',
            'invoices.create',
            'invoices.update',
            'invoices.issue',
            'invoices.print',
            'payments.view',
            'payments.create',
            'payment-methods.view',
            'currencies.view',
            'receipts.view',
            'orders.view',
            'orders.create',
            'orders.update',
            'products.view',
            'products.create',
            'products.update',
            'measurements.view',
            'measurements.create',
            'measurements.update',
            'settings.profile.update',
            'settings.password.update',
            'settings.appearance.update',
            // Inventory Permissions for Sales
            'units-of-measure.view',
            'stock-locations.view',
            'inventory-batches.view',
            'inventory-movements.view',
            'inventory-movements.create',
            'inventory-transfers.create',
        ]);

        $accountant->syncPermissions([
            'dashboard.view',
            'customers.view',
            'invoices.view',
            'invoices.print',
            'payments.view',
            'payments.create',
            'payments.void',
            'payment-methods.view',
            'payment-methods.create',
            'payment-methods.update',
            'payment-methods.delete',
            'currencies.view',
            'currencies.create',
            'currencies.update',
            'currencies.delete',
            'receipts.view',
            'expenses.view',
            'expenses.create',
            'expenses.update',
            'expenses.void',
            'reports.view',
            'activity-logs.view',
            'settings.profile.update',
            'settings.password.update',
            'settings.appearance.update',
            // Inventory Permissions for Accountant
            'units-of-measure.view',
            'units-of-measure.create',
            'units-of-measure.update',
            'stock-locations.view',
            'inventory-batches.view',
            'inventory-movements.view',
            'inventory-movements.create',
            'inventory-transfers.create',
        ]);

        $tailor->syncPermissions([
            'dashboard.view',
            'customers.view',
            'orders.view',
            'orders.update',
            'measurements.view',
            'settings.profile.update',
            'settings.password.update',
            'settings.appearance.update',
        ]);
    }
}
