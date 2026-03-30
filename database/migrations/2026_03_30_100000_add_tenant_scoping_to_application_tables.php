<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'users',
            'customers',
            'currencies',
            'payment_methods',
            'product_categories',
            'products',
            'expense_categories',
            'expenses',
            'orders',
            'order_items',
            'invoices',
            'invoice_items',
            'payments',
            'receipts',
            'measurements',
            'activity_log',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->string('tenant_id')->nullable()->index();
                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnUpdate()->cascadeOnDelete();
            });
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique('users_email_unique');
            $table->unique(['tenant_id', 'email']);
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->dropUnique('customers_customer_code_unique');
            $table->dropUnique('customers_phone_unique');
            $table->dropUnique('customers_email_unique');
            $table->unique(['tenant_id', 'customer_code']);
            $table->unique(['tenant_id', 'phone']);
            $table->unique(['tenant_id', 'email']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique('orders_order_number_unique');
            $table->unique(['tenant_id', 'order_number']);
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropUnique('invoices_invoice_number_unique');
            $table->unique(['tenant_id', 'invoice_number']);
        });

        Schema::table('receipts', function (Blueprint $table): void {
            $table->dropUnique('receipts_receipt_number_unique');
            $table->unique(['tenant_id', 'receipt_number']);
        });

        Schema::table('expense_categories', function (Blueprint $table): void {
            $table->dropUnique('expense_categories_name_unique');
            $table->unique(['tenant_id', 'name']);
        });

        Schema::table('currencies', function (Blueprint $table): void {
            $table->dropUnique('currencies_code_unique');
            $table->unique(['tenant_id', 'code']);
        });

        Schema::table('payment_methods', function (Blueprint $table): void {
            $table->dropUnique('payment_methods_name_unique');
            $table->dropUnique('payment_methods_slug_unique');
            $table->unique(['tenant_id', 'name']);
            $table->unique(['tenant_id', 'slug']);
        });

        Schema::table('product_categories', function (Blueprint $table): void {
            $table->dropUnique('product_categories_name_unique');
            $table->unique(['tenant_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table): void {
            $table->dropUnique(['tenant_id', 'name']);
            $table->unique('name');
        });

        Schema::table('payment_methods', function (Blueprint $table): void {
            $table->dropUnique(['tenant_id', 'name']);
            $table->dropUnique(['tenant_id', 'slug']);
            $table->unique('name');
            $table->unique('slug');
        });

        Schema::table('currencies', function (Blueprint $table): void {
            $table->dropUnique(['tenant_id', 'code']);
            $table->unique('code');
        });

        Schema::table('expense_categories', function (Blueprint $table): void {
            $table->dropUnique(['tenant_id', 'name']);
            $table->unique('name');
        });

        Schema::table('receipts', function (Blueprint $table): void {
            $table->dropUnique(['tenant_id', 'receipt_number']);
            $table->unique('receipt_number');
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropUnique(['tenant_id', 'invoice_number']);
            $table->unique('invoice_number');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique(['tenant_id', 'order_number']);
            $table->unique('order_number');
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->dropUnique(['tenant_id', 'customer_code']);
            $table->dropUnique(['tenant_id', 'phone']);
            $table->dropUnique(['tenant_id', 'email']);
            $table->unique('customer_code');
            $table->unique('phone');
            $table->unique('email');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['tenant_id', 'email']);
            $table->unique('email');
        });

        $tables = [
            'activity_log',
            'measurements',
            'receipts',
            'payments',
            'invoice_items',
            'invoices',
            'order_items',
            'orders',
            'expenses',
            'expense_categories',
            'products',
            'product_categories',
            'payment_methods',
            'currencies',
            'customers',
            'users',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};
