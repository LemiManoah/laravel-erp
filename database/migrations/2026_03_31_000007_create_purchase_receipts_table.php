<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->foreignId('stock_location_id')->constrained('stock_locations')->restrictOnDelete();
            $table->string('receipt_number');
            $table->date('receipt_date');
            $table->string('status')->default('posted');
            $table->decimal('subtotal_amount', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unique(['tenant_id', 'receipt_number']);
            $table->index(['tenant_id', 'receipt_date']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'supplier_id', 'status', 'receipt_date'], 'purchase_receipts_supplier_status_idx');
            $table->index(['tenant_id', 'stock_location_id', 'receipt_date'], 'purchase_receipts_location_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_receipts');
    }
};
