<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCheckInRequest;
use App\Services\CheckInService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    public function __construct(
        private CheckInService $checkInService
    ) {
    }

    /**
     * Store a new check-in.
     */
    public function store(StoreCheckInRequest $request): JsonResponse
    {
        $checkin = $this->checkInService->processCheckIn(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Check-in đã được lưu thành công.',
            'data' => $checkin,
        ], 201);
    }

    /**
     * Get user's check-ins.
     */
    public function index(Request $request): JsonResponse
    {
        $checkins = $request->user()->dailyCheckins()
            ->orderBy('checkin_date', 'desc')
            ->paginate(30);

        return response()->json($checkins);
    }
}
