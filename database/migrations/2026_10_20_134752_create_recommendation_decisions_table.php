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
        Schema::create('recommendation_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recommendation_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('decision', 32);
            $table->text('comment')->nullable();
            $table->json('decision_snapshot');
            $table->timestamp('decided_at');
            $table->timestamps();
            $table->index(['user_id', 'decided_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recommendation_decisions');
    }
};
