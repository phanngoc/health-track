<?php

namespace App\Http\Controllers;

use App\Models\DailyCheckin;
use App\Models\Symptom;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(): \Illuminate\Contracts\View\View
    {
        $user = Auth::user();

        $hasTodayCheckin = false;
        $recentCheckins = collect();

        if ($user) {
            // Check if user has check-in today
            $hasTodayCheckin = DailyCheckin::where('user_id', $user->id)
                ->where('checkin_date', Carbon::today())
                ->exists();

            // Get recent check-ins (last 7 days, max 3 items)
            // Order by created_at để hiển thị check-ins mới nhất (kể cả nhiều check-in cùng ngày)
            $recentCheckins = DailyCheckin::where('user_id', $user->id)
                ->where('checkin_date', '>=', Carbon::today()->subDays(7))
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();
        }

        $symptoms = Symptom::orderBy('display_name')->get();

        return view('home', [
            'user' => $user,
            'hasTodayCheckin' => $hasTodayCheckin,
            'recentCheckins' => $recentCheckins,
            'symptoms' => $symptoms,
        ]);
    }
}
