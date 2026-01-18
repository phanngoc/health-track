<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCheckInRequest;
use App\Http\Requests\StoreMomentCheckInRequest;
use App\Services\CheckInService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    public function __construct(
        private CheckInService $checkInService
    ) {}

    /**
     * Store a new daily check-in.
     */
    public function store(StoreCheckInRequest $request): JsonResponse
    {
        $checkin = $this->checkInService->processDailyCheckIn(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Check-in đã được lưu thành công.',
            'data' => $checkin,
        ], 201);
    }

    /**
     * Store a new moment check-in.
     */
    public function storeMoment(StoreMomentCheckInRequest $request): JsonResponse
    {
        $momentCheckin = $this->checkInService->processMomentCheckIn(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Moment check-in đã được lưu thành công.',
            'data' => $momentCheckin,
        ], 201);
    }

    /**
     * Get user's check-ins (both daily and moment).
     */
    public function index(Request $request): JsonResponse
    {
        $dailyCheckins = $request->user()->dailyCheckins()
            ->orderBy('checkin_date', 'desc')
            ->paginate(30);

        $momentCheckins = $request->user()->momentCheckins()
            ->orderBy('occurred_at', 'desc')
            ->paginate(30);

        return response()->json([
            'daily_checkins' => $dailyCheckins,
            'moment_checkins' => $momentCheckins,
        ]);
    }
}
