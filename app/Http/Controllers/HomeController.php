<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\DailyCheckin;
use App\Models\SymptomLog;
use App\Models\TimelineEvent;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(): \Illuminate\Contracts\View\View
    {
        $user = Auth::user();

        $stats = [
            'total_checkins' => 0,
            'total_symptoms' => 0,
            'active_alerts' => 0,
            'timeline_events' => 0,
        ];

        if ($user) {
            $stats['total_checkins'] = DailyCheckin::where('user_id', $user->id)->count();
            $stats['total_symptoms'] = SymptomLog::where('user_id', $user->id)->count();
            $stats['active_alerts'] = Alert::where('user_id', $user->id)
                ->whereNull('acknowledged_at')
                ->count();
            $stats['timeline_events'] = TimelineEvent::where('user_id', $user->id)
                ->where('occurred_at', '>=', now()->subDays(7))
                ->count();
        }

        return view('home', compact('stats'));
    }
}
