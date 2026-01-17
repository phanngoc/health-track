<?php

namespace App\Http\Controllers;

use App\Models\Insight;
use App\Services\InsightService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InsightController extends Controller
{
    public function __construct(
        private InsightService $insightService
    ) {}

    /**
     * Show explanation for an insight.
     */
    public function explain(Request $request, Insight $insight): View
    {
        // Ensure user owns this insight
        if ($insight->user_id !== $request->user()->id) {
            abort(403);
        }

        return view('insights.explain', [
            'insight' => $insight,
        ]);
    }
}
