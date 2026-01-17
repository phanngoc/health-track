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
        Schema::create('insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // TREND, PATTERN, COMPARISON, CONTEXTUAL, REASSURANCE
            $table->string('code'); // INS_TREND_WORSENING, etc.
            $table->text('message');
            $table->string('priority')->default('medium'); // low, medium, high
            $table->json('metadata')->nullable(); // Additional data
            $table->json('explanation_data')->nullable(); // Data for explainability
            $table->timestamp('generated_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'generated_at']);
            $table->index(['user_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insights');
    }
};
