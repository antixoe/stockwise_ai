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
        Schema::create('forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->uuid('run_uuid');
            $table->date('forecast_date');
            $table->date('data_as_of');
            $table->decimal('predicted_quantity', 18, 3);
            $table->decimal('lower_bound', 18, 3)->nullable();
            $table->decimal('upper_bound', 18, 3)->nullable();
            $table->string('unit', 32);
            $table->string('model_name');
            $table->string('model_version');
            $table->string('horizon', 32);
            $table->string('status', 32)->default('queued')->index();
            $table->decimal('mae', 18, 6)->nullable();
            $table->decimal('wape', 18, 6)->nullable();
            $table->json('input_snapshot')->nullable();
            $table->timestamps();
            $table->unique(['run_uuid', 'product_id', 'forecast_date']);
            $table->index(['product_id', 'forecast_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forecasts');
    }
};
