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
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('forecast_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('recommendation_uuid')->unique();
            $table->string('status', 32)->default('pending')->index();
            $table->date('recommended_order_date');
            $table->date('expected_receipt_date')->nullable();
            $table->decimal('reorder_quantity', 18, 3);
            $table->decimal('demand_during_lead_time', 18, 3);
            $table->decimal('on_hand_at_calculation', 18, 3);
            $table->decimal('reserved_at_calculation', 18, 3);
            $table->decimal('incoming_at_calculation', 18, 3);
            $table->decimal('safety_stock_at_calculation', 18, 3);
            $table->decimal('lead_time_days', 10, 2);
            $table->string('rule_version');
            $table->json('input_snapshot');
            $table->text('rationale');
            $table->timestamp('generated_at');
            $table->timestamps();
            $table->index(['product_id', 'status']);
            $table->index(['recommended_order_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
