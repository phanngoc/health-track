<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TimelineService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimelineController extends Controller
{
    public function __construct(
        private TimelineService $timelineService
    ) {
    }

    /**
     * Get user's timeline.
     */
    public function index(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        $grouped = $request->boolean('grouped', false);

        if ($grouped) {
            $timeline = $this->timelineService->getTimelineGroupedByDate(
                $request->user(),
                $startDate,
                $endDate
            );
        } else {
            $timeline = $this->timelineService->getTimeline(
                $request->user(),
                $startDate,
                $endDate
            );
        }

        return response()->json([
            'data' => $timeline,
        ]);
    }
}
