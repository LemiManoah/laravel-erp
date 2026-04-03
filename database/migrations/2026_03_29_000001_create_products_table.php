<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('product_category_id')->nullable()->constrained()->onDelete('restrict');
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->string('item_type')->default('stock_item');
            $table->boolean('tracks_inventory')->default(true);
            $table->boolean('is_sellable')->default(true);
            $table->boolean('is_purchasable')->default(true);
            $table->unsignedBigInteger('base_unit_id')->nullable();
            $table->decimal('reorder_level', 12, 2)->nullable();
            $table->boolean('has_variants')->default(false);
            $table->foreignId('parent_item_id')->nullable()->constrained('products')->onDelete('restrict');
            $table->boolean('has_expiry')->default(false);
            $table->boolean('is_serialized')->default(false);
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unique(['tenant_id', 'sku']);
            $table->unique(['tenant_id', 'barcode']);
            $table->index(['tenant_id', 'item_type']);
            $table->index(['tenant_id', 'is_active', 'name']);
            $table->index(['tenant_id', 'product_category_id', 'is_active']);
            $table->index(['tenant_id', 'tracks_inventory', 'is_active']);
            $table->index(['tenant_id', 'is_purchasable', 'is_active']);
            $table->index(['tenant_id', 'is_sellable', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
