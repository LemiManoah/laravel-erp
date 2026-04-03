<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('inventory_item_id')->constrained()->onDelete('restrict');
            $table->foreignId('location_id')->nullable()->constrained('stock_locations')->onDelete('restrict');
            $table->unsignedBigInteger('inventory_stock_id')->nullable();
            $table->string('movement_type');
            $table->string('direction');
            $table->decimal('quantity', 12, 2);
            $table->foreignId('unit_id')->nullable()->constrained('units_of_measure')->onDelete('restrict');
            $table->decimal('unit_conversion_rate', 12, 4)->default(1);
            $table->decimal('balance_after', 12, 2)->nullable();
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->datetime('movement_date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnUpdate()->cascadeOnDelete();
            $table->index(['tenant_id', 'inventory_item_id']);
            $table->index(['tenant_id', 'movement_type']);
            $table->index(['tenant_id', 'movement_date']);
            $table->index(['tenant_id', 'inventory_stock_id']);
            $table->index(['tenant_id', 'location_id', 'movement_date'], 'inv_movements_tenant_loc_date_idx');
            $table->index(['tenant_id', 'movement_type', 'direction', 'movement_date'], 'inv_movements_type_dir_date_idx');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
