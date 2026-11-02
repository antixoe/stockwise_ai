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
        Schema::create('sales_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->date('demand_date');
            $table->decimal('quantity_demanded', 18, 3);
            $table->decimal('quantity_sold', 18, 3)->nullable();
            $table->decimal('unit_price', 19, 4)->nullable();
            $table->boolean('promotion_flag')->nullable();
            $table->decimal('stock_available', 18, 3)->nullable();
            $table->string('source');
            $table->json('features')->nullable();
            $table->timestamps();
            $table->unique(['product_id', 'demand_date', 'source']);
            $table->index(['product_id', 'demand_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_history');
    }
};
