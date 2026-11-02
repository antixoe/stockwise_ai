<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->string('supplier_sku')->nullable();
            $table->decimal('lead_time_days', 10, 2);
            $table->decimal('minimum_order_quantity', 18, 3)->nullable();
            $table->decimal('order_multiple', 18, 3)->nullable();
            $table->decimal('last_unit_cost', 19, 4)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->unique(['product_id', 'supplier_id']);
            $table->index(['product_id', 'is_primary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_suppliers');
    }
};
