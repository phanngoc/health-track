<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\DailyCheckin;
use App\Models\Insight;
use App\Models\SymptomLog;
use App\Services\InsightService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimelineController extends Controller
{
    public function __construct(
        private InsightService $insightService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        // Layer 1: Health Status Header (use new generateInsights, fallback to old method)
        $newInsights = $this->insightService->generateInsights($user);
        $oldInsight = $this->insightService->generateInsight($user);

        // Save insights to database
        foreach ($newInsights as $insightData) {
            Insight::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'code' => $insightData['code'],
                    'generated_at' => Carbon::today(),
                ],
                [
                    'type' => $insightData['type'],
                    'message' => $insightData['message'],
                    'priority' => $insightData['priority'],
                    'metadata' => $insightData['metadata'] ?? [],
                    'explanation_data' => $insightData['explanation_data'] ?? [],
                    'expires_at' => Carbon::today()->addDays(1),
                ]
            );
        }

        // Get primary insight for header (prefer TREND or REASSURANCE, max 1 sentence)
        $headerInsight = null;
        if (! empty($newInsights)) {
            // Find TREND or REASSURANCE insight
            foreach ($newInsights as $insight) {
                if (in_array($insight['type'], ['TREND', 'REASSURANCE'])) {
                    $headerInsight = $insight;
                    break;
                }
            }
            // If no TREND/REASSURANCE, use first insight
            if (! $headerInsight) {
                $headerInsight = $newInsights[0];
            }
        }

        // Layer 2: Visual Trend Strip (7 days)
        $trendStrip = $this->getTrendStrip($user);

        // Layer 3: Event Timeline (last 7 days, meaningful events only)
        $events = $this->getMeaningfulEvents($user);

        // Add insights to timeline events
        $insightEvents = $this->getInsightEvents($user);
        $events = $this->mergeInsightsIntoEvents($events, $insightEvents);

        return view('timeline.index', [
            'insight' => $oldInsight, // Keep for backward compatibility
            'insights' => $newInsights, // New insights array
            'headerInsight' => $headerInsight, // Primary insight for header
            'trendStrip' => $trendStrip,
            'events' => $events,
            'insightService' => $this->insightService,
        ]);
    }

    /**
     * Get trend strip data for last 7 days.
     */
    private function getTrendStrip($user): array
    {
        $days = [];
        $today = Carbon::today();

        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $dateStr = $date->format('Y-m-d');

            // Get daily severity (max severity of symptoms that day)
            $maxSeverity = SymptomLog::where('user_id', $user->id)
                ->whereDate('occurred_at', $date)
                ->max('severity') ?? 0;

            // Get check-in feeling if available
            $checkin = DailyCheckin::where('user_id', $user->id)
                ->where('checkin_date', $date)
                ->first();

            // Calculate composite severity (0-10)
            $compositeSeverity = $this->calculateCompositeSeverity($maxSeverity, $checkin);

            // Determine color
            $color = $this->getSeverityColor($compositeSeverity);

            $days[] = [
                'date' => $date,
                'label' => $i === 0 ? 'Hôm nay' : ($i === 1 ? 'Hôm qua' : $date->format('d/m')),
                'severity' => $compositeSeverity,
                'height' => $this->getBarHeight($compositeSeverity),
                'color' => $color,
                'hasCheckin' => $checkin !== null,
            ];
        }

        return $days;
    }

    /**
     * Calculate composite severity from symptom severity and check-in feeling.
     */
    private function calculateCompositeSeverity(int $maxSymptomSeverity, ?DailyCheckin $checkin): float
    {
        $symptomWeight = 0.6;
        $feelingWeight = 0.4;

        $symptomScore = $maxSymptomSeverity;

        // Convert feeling (1-10) to severity (inverse: 10 = good = 0 severity, 1 = bad = 10 severity)
        $feelingScore = $checkin && $checkin->overall_feeling
            ? (11 - $checkin->overall_feeling) // Invert: 10 feeling = 1 severity, 1 feeling = 10 severity
            : 5; // Default if no check-in

        return round(($symptomScore * $symptomWeight) + ($feelingScore * $feelingWeight), 1);
    }

    /**
     * Get bar height percentage (0-100).
     */
    private function getBarHeight(float $severity): int
    {
        // Map 0-10 severity to 20-100% height (min 20% for visibility)
        return (int) (20 + ($severity / 10) * 80);
    }

    /**
     * Get color based on severity.
     */
    private function getSeverityColor(float $severity): string
    {
        if ($severity >= 7) {
            return 'red';
        } elseif ($severity >= 5) {
            return 'orange';
        } elseif ($severity >= 3) {
            return 'yellow';
        }

        return 'green';
    }

    /**
     * Get meaningful events only (last 7 days).
     */
    private function getMeaningfulEvents($user): array
    {
        $startDate = Carbon::today()->subDays(7);
        $events = [];

        // Get alerts (always meaningful)
        $alerts = Alert::where('user_id', $user->id)
            ->where('triggered_at', '>=', $startDate)
            ->orderBy('triggered_at', 'desc')
            ->get();

        foreach ($alerts as $alert) {
            $events[] = [
                'type' => 'alert',
                'date' => $alert->triggered_at->format('Y-m-d'),
                'time' => $alert->triggered_at,
                'icon' => $this->getAlertIcon($alert->severity),
                'color' => $this->getAlertColor($alert->severity),
                'title' => 'Cảnh báo: '.strtoupper($alert->severity),
                'message' => $alert->message,
                'data' => $alert,
            ];
        }

        // Get check-ins with symptoms (meaningful)
        $checkins = DailyCheckin::where('user_id', $user->id)
            ->where('checkin_date', '>=', $startDate)
            ->orderBy('checkin_date', 'desc')
            ->get();

        foreach ($checkins as $checkin) {
            $symptoms = SymptomLog::where('user_id', $user->id)
                ->whereDate('occurred_at', $checkin->checkin_date)
                ->where('source', 'checkin')
                ->with('symptom')
                ->get();

            if ($symptoms->count() > 0 || $checkin->overall_feeling <= 5) {
                $events[] = [
                    'type' => 'checkin',
                    'date' => $checkin->checkin_date->format('Y-m-d'),
                    'time' => $checkin->created_at,
                    'icon' => '📝',
                    'color' => 'blue',
                    'title' => 'Check-in Hằng Ngày',
                    'data' => $checkin,
                    'symptoms' => $symptoms,
                ];
            }
        }

        // Get significant symptoms (severity >= 5 or critical)
        $significantSymptoms = SymptomLog::where('user_id', $user->id)
            ->where('occurred_at', '>=', $startDate)
            ->where(function ($query) {
                $query->where('severity', '>=', 5)
                    ->orWhereHas('symptom', function ($q) {
                        $q->where('is_critical', true);
                    });
            })
            ->with('symptom')
            ->orderBy('occurred_at', 'desc')
            ->get()
            ->unique(function ($log) {
                return $log->symptom_code.'-'.$log->occurred_at->format('Y-m-d');
            });

        foreach ($significantSymptoms as $log) {
            // Skip if already included in check-in
            $alreadyIncluded = collect($events)->contains(function ($event) use ($log) {
                return $event['type'] === 'checkin'
                    && $event['date'] === $log->occurred_at->format('Y-m-d');
            });

            if (! $alreadyIncluded) {
                $events[] = [
                    'type' => 'symptom',
                    'date' => $log->occurred_at->format('Y-m-d'),
                    'time' => $log->occurred_at,
                    'icon' => $log->symptom->is_critical ?? false ? '🩹' : '🤧',
                    'color' => $log->severity >= 7 ? 'red' : ($log->severity >= 5 ? 'orange' : 'yellow'),
                    'title' => ($log->symptom->display_name ?? $log->symptom_code).': '.$log->severity.'/10',
                    'data' => $log,
                ];
            }
        }

        // Sort by time (descending) and group by date
        usort($events, function ($a, $b) {
            return $b['time']->timestamp <=> $a['time']->timestamp;
        });

        $grouped = collect($events)->groupBy('date')->map(function ($items, $date) {
            return [
                'date' => Carbon::parse($date),
                'items' => $items->values()->all(),
            ];
        })->sortKeysDesc();

        return $grouped->values()->all();
    }

    private function getAlertIcon(string $severity): string
    {
        return match ($severity) {
            'critical' => '🚨',
            'warning' => '⚠️',
            'watch' => '👀',
            default => 'ℹ️',
        };
    }

    private function getAlertColor(string $severity): string
    {
        return match ($severity) {
            'critical' => 'red',
            'warning' => 'orange',
            'watch' => 'yellow',
            default => 'blue',
        };
    }

    /**
     * Get insight events for timeline.
     */
    private function getInsightEvents($user): array
    {
        $startDate = Carbon::today()->subDays(7);
        $insights = Insight::where('user_id', $user->id)
            ->where('generated_at', '>=', $startDate)
            ->orderBy('generated_at', 'desc')
            ->get();

        $events = [];
        foreach ($insights as $insight) {
            $priorityColors = [
                'high' => 'red',
                'medium' => 'orange',
                'low' => 'blue',
            ];

            $events[] = [
                'type' => 'insight',
                'date' => $insight->generated_at->format('Y-m-d'),
                'time' => $insight->generated_at,
                'icon' => '🧠',
                'color' => $priorityColors[$insight->priority] ?? 'blue',
                'title' => 'Insight: '.$insight->type,
                'message' => $insight->message,
                'data' => $insight,
            ];
        }

        return $events;
    }

    /**
     * Merge insights into events timeline.
     */
    private function mergeInsightsIntoEvents(array $events, array $insightEvents): array
    {
        // Flatten grouped events first
        $flatEvents = [];
        foreach ($events as $dayGroup) {
            foreach ($dayGroup['items'] as $event) {
                $flatEvents[] = $event;
            }
        }

        // Combine all events
        $allEvents = array_merge($flatEvents, $insightEvents);

        // Re-sort by time
        usort($allEvents, function ($a, $b) {
            $timeA = $a['time'] ?? Carbon::parse($a['date'] ?? now());
            $timeB = $b['time'] ?? Carbon::parse($b['date'] ?? now());

            if ($timeA instanceof Carbon && $timeB instanceof Carbon) {
                return $timeB->timestamp <=> $timeA->timestamp;
            }

            return 0;
        });

        // Re-group by date
        $grouped = collect($allEvents)->groupBy('date')->map(function ($items, $date) {
            return [
                'date' => Carbon::parse($date),
                'items' => $items->values()->all(),
            ];
        })->sortKeysDesc();

        return $grouped->values()->all();
    }
}
