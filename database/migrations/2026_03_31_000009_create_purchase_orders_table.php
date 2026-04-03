<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->foreignId('stock_location_id')->constrained('stock_locations')->restrictOnDelete();
            $table->string('order_number');
            $table->date('order_date');
            $table->date('expected_date')->nullable();
            $table->string('status')->default('ordered');
            $table->decimal('subtotal_amount', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unique(['tenant_id', 'order_number']);
            $table->index(['tenant_id', 'order_date']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'supplier_id', 'status', 'order_date']);
            $table->index(['tenant_id', 'stock_location_id', 'order_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
