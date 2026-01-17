<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    /**
     * Get user's alerts.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Alert::where('user_id', $request->user()->id)
            ->orderBy('triggered_at', 'desc');

        if ($request->has('severity')) {
            $query->where('severity', $request->input('severity'));
        }

        if ($request->has('unacknowledged')) {
            $query->whereNull('acknowledged_at');
        }

        $alerts = $query->paginate(30);

        return response()->json($alerts);
    }

    /**
     * Acknowledge an alert.
     */
    public function acknowledge(Request $request, Alert $alert): JsonResponse
    {
        if ($alert->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Không có quyền truy cập.'], 403);
        }

        $alert->update([
            'acknowledged_at' => now(),
        ]);

        return response()->json([
            'message' => 'Đã xác nhận cảnh báo.',
            'data' => $alert,
        ]);
    }

    /**
     * Get unacknowledged alerts count.
     */
    public function unacknowledgedCount(Request $request): JsonResponse
    {
        $count = Alert::where('user_id', $request->user()->id)
            ->whereNull('acknowledged_at')
            ->count();

        return response()->json([
            'count' => $count,
        ]);
    }
}
