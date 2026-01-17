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
        Schema::create('symptom_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('symptom_code'); // references symptoms.code
            $table->integer('severity'); // 0-10
            $table->timestamp('occurred_at');
            $table->string('source')->default('checkin'); // checkin | manual | auto
            $table->timestamps();

            $table->index(['user_id', 'occurred_at']);
            $table->index('symptom_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('symptom_logs');
    }
};
