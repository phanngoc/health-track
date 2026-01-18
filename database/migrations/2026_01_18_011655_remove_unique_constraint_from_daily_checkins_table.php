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
            $table->dropUnique('daily_checkins_user_id_checkin_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_checkins', function (Blueprint $table) {
            $table->unique(['user_id', 'checkin_date'], 'daily_checkins_user_id_checkin_date_unique');
        });
    }
};
