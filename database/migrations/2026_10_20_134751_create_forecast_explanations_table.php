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
        Schema::create('forecast_explanations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forecast_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('explainer_name');
            $table->string('explainer_version')->nullable();
            $table->json('feature_contributions');
            $table->json('base_value')->nullable();
            $table->string('status', 32)->default('pending')->index();
            $table->text('limitation_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forecast_explanations');
    }
};
