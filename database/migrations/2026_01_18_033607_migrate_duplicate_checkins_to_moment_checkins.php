<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Find all user_id + checkin_date combinations with multiple daily_checkins
        $duplicates = DB::table('daily_checkins')
            ->select('user_id', 'checkin_date', DB::raw('COUNT(*) as count'))
            ->groupBy('user_id', 'checkin_date')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            // Get all check-ins for this user/date combination, ordered by created_at desc
            $checkins = DB::table('daily_checkins')
                ->where('user_id', $duplicate->user_id)
                ->where('checkin_date', $duplicate->checkin_date)
                ->orderBy('created_at', 'desc')
                ->get();

            // Keep the latest one (first in the list)
            $latestCheckin = $checkins->first();
            $others = $checkins->skip(1);

            // Move all others to moment_checkins
            foreach ($others as $checkin) {
                DB::table('moment_checkins')->insert([
                    'user_id' => $checkin->user_id,
                    'feeling_level' => $checkin->overall_feeling,
                    'mood' => $checkin->mood,
                    'tags' => $checkin->tags,
                    'occurred_at' => $checkin->created_at,
                    'created_at' => $checkin->created_at,
                    'updated_at' => $checkin->updated_at,
                ]);

                // Delete the duplicate daily_checkin
                DB::table('daily_checkins')->where('id', $checkin->id)->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not easily reversible
        // We would need to track which moment_checkins came from daily_checkins
        // For now, we'll leave it empty as data migrations are typically one-way
    }
};
