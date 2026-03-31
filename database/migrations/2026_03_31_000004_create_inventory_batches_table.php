<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->foreignId('location_id')->nullable()->constrained('stock_locations')->onDelete('restrict');
            $table->string('batch_number');
            $table->date('expiry_date')->nullable();
            $table->date('manufactured_at')->nullable();
            $table->date('received_at')->nullable();
            $table->decimal('quantity_on_hand', 12, 2)->default(0);
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unique(['tenant_id', 'product_id', 'location_id', 'batch_number'], 'inventory_batches_unique_batch');
            $table->index(['tenant_id', 'expiry_date']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_batches');
    }
};
