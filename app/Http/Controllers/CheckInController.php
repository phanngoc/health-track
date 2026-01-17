<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCheckInRequest;
use App\Models\Symptom;
use App\Services\CheckInService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckInController extends Controller
{
    public function __construct(
        private CheckInService $checkInService
    ) {
    }

    public function index(Request $request): View
    {
        $checkins = $request->user()->dailyCheckins()
            ->orderBy('checkin_date', 'desc')
            ->paginate(10);

        // Load symptom logs for each check-in
        foreach ($checkins as $checkin) {
            $checkin->symptomLogs = $request->user()->symptomLogs()
                ->whereDate('occurred_at', $checkin->checkin_date)
                ->where('source', 'checkin')
                ->with('symptom')
                ->get();
        }

        return view('checkins.index', compact('checkins'));
    }

    public function create(): View
    {
        $symptoms = Symptom::orderBy('display_name')->get();
        $todayCheckin = auth()->user()->dailyCheckins()
            ->where('checkin_date', today())
            ->first();

        // Load symptom logs if check-in exists
        if ($todayCheckin) {
            $todayCheckin->symptomLogs = auth()->user()->symptomLogs()
                ->whereDate('occurred_at', today())
                ->where('source', 'checkin')
                ->with('symptom')
                ->get();
        }

        return view('checkins.create', compact('symptoms', 'todayCheckin'));
    }

    public function store(StoreCheckInRequest $request): RedirectResponse
    {
        $checkin = $this->checkInService->processCheckIn(
            $request->user(),
            $request->validated()
        );

        return redirect()->route('checkins.index')
            ->with('success', 'Check-in đã được lưu thành công!');
    }
}
