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
        Schema::table('daily_checkins', function (Blueprint $table) {
            $table->string('mood')->nullable()->after('checkin_date');
            $table->json('tags')->nullable()->after('mood');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_checkins', function (Blueprint $table) {
            $table->dropColumn(['mood', 'tags']);
        });
    }
};
