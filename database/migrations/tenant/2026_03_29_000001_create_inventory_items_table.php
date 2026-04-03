<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_category_id')->nullable()->constrained()->onDelete('restrict');
            $table->string('sku')->nullable()->unique();
            $table->string('barcode')->nullable()->unique();
            $table->string('item_type')->default('stock_item');
            $table->boolean('tracks_inventory')->default(true);
            $table->boolean('is_sellable')->default(true);
            $table->boolean('is_purchasable')->default(true);
            $table->unsignedBigInteger('base_unit_id')->nullable();
            $table->decimal('reorder_level', 12, 2)->nullable();
            $table->boolean('has_variants')->default(false);
            $table->foreignId('parent_item_id')->nullable()->constrained('inventory_items')->onDelete('restrict');
            $table->boolean('has_expiry')->default(false);
            $table->boolean('is_serialized')->default(false);
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};

