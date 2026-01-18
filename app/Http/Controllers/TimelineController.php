<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\DailyCheckin;
use App\Models\Insight;
use App\Models\MomentCheckin;
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

        // Layer 3: Event Timeline with 3-level hierarchy (last 7 days)
        $daysData = $this->getStructuredDaysData($user);

        return view('timeline.index', [
            'insight' => $oldInsight, // Keep for backward compatibility
            'insights' => $newInsights, // New insights array
            'headerInsight' => $headerInsight, // Primary insight for header
            'trendStrip' => $trendStrip,
            'daysData' => $daysData,
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
     * Get structured days data with 3-level hierarchy (last 7 days).
     */
    private function getStructuredDaysData($user): array
    {
        $startDate = Carbon::today()->subDays(7);
        $today = Carbon::today();
        $daysData = [];

        // Get all events first
        $allEvents = $this->getAllEvents($user, $startDate);
        $insightEvents = $this->getInsightEvents($user);
        $allEvents = array_merge($allEvents, $insightEvents);

        // Group events by date
        $eventsByDate = collect($allEvents)->groupBy('date');

        // Process each day
        for ($i = 0; $i <= 6; $i++) {
            $date = $today->copy()->subDays(6 - $i);
            $dateStr = $date->format('Y-m-d');

            $dayEvents = $eventsByDate->get($dateStr, collect())->sortByDesc(function ($event) {
                return $event['time']->timestamp;
            })->values()->all();

            // Get daily summary
            $dailySummary = $this->getDailySummary($user, $date);

            // Separate events by level
            $level2Events = [];
            $level3Events = [];

            foreach ($dayEvents as $event) {
                if ($event['type'] === 'moment_checkin') {
                    $level3Events[] = $event;
                } else {
                    $level2Events[] = $event;
                }
            }

            // Group moment check-ins
            $momentGroup = $this->groupMomentCheckins($level3Events);

            $daysData[] = [
                'date' => $date,
                'daily_summary' => $dailySummary,
                'level_2_events' => $level2Events,
                'level_3_events' => $momentGroup,
            ];
        }

        // Reverse to show most recent first
        return array_reverse($daysData);
    }

    /**
     * Get all events (last 7 days).
     */
    private function getAllEvents($user, $startDate): array
    {
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

        // Get daily check-ins (baseline, 1 per day)
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

            // Always show daily check-in (it's Level 2 - baseline)
            $events[] = [
                'type' => 'checkin',
                'date' => $checkin->checkin_date->format('Y-m-d'),
                'time' => $checkin->created_at,
                'icon' => '📝',
                'color' => 'blue',
                'title' => 'Check-in Hằng Ngày',
                'message' => $checkin->overall_feeling ? "Cảm giác tổng thể: {$checkin->overall_feeling}/10" : null,
                'data' => $checkin,
                'symptoms' => $symptoms,
            ];
        }

        // Get moment check-ins (quick mood tracking, multiple per day)
        $momentCheckins = MomentCheckin::where('user_id', $user->id)
            ->where('occurred_at', '>=', $startDate)
            ->orderBy('occurred_at', 'desc')
            ->get();

        foreach ($momentCheckins as $momentCheckin) {
            $tagsDisplay = $momentCheckin->tags && is_array($momentCheckin->tags)
                ? ' ('.implode(' ', $momentCheckin->tags).')'
                : '';

            // Load symptom logs for this moment check-in
            $symptoms = SymptomLog::where('user_id', $user->id)
                ->where('source', 'moment_checkin')
                ->whereBetween('occurred_at', [
                    $momentCheckin->occurred_at->copy()->subSeconds(5),
                    $momentCheckin->occurred_at->copy()->addSeconds(5),
                ])
                ->with('symptom')
                ->get();

            $events[] = [
                'type' => 'moment_checkin',
                'date' => $momentCheckin->occurred_at->format('Y-m-d'),
                'time' => $momentCheckin->occurred_at,
                'icon' => $momentCheckin->mood ?? '😐',
                'color' => 'gray',
                'title' => $momentCheckin->mood ?? 'Moment Check-in',
                'message' => $momentCheckin->feeling_level
                    ? "Cảm giác lúc {$momentCheckin->occurred_at->format('H:i')}: {$momentCheckin->feeling_level}/10{$tagsDisplay}"
                    : $tagsDisplay,
                'data' => $momentCheckin,
                'symptoms' => $symptoms,
            ];
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

        return $events;
    }

    /**
     * Get meaningful events only (last 7 days).
     *
     * @deprecated Use getStructuredDaysData instead
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

        // Get daily check-ins (baseline, 1 per day)
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
                    'message' => $checkin->overall_feeling ? "Cảm giác tổng thể: {$checkin->overall_feeling}/10" : null,
                    'data' => $checkin,
                    'symptoms' => $symptoms,
                ];
            }
        }

        // Get moment check-ins (quick mood tracking, multiple per day)
        $momentCheckins = MomentCheckin::where('user_id', $user->id)
            ->where('occurred_at', '>=', $startDate)
            ->orderBy('occurred_at', 'desc')
            ->get();

        foreach ($momentCheckins as $momentCheckin) {
            $tagsDisplay = $momentCheckin->tags && is_array($momentCheckin->tags)
                ? ' ('.implode(' ', $momentCheckin->tags).')'
                : '';

            // Load symptom logs for this moment check-in
            // Match symptoms created within 5 seconds of the moment check-in (same transaction)
            $symptoms = SymptomLog::where('user_id', $user->id)
                ->where('source', 'moment_checkin')
                ->whereBetween('occurred_at', [
                    $momentCheckin->occurred_at->copy()->subSeconds(5),
                    $momentCheckin->occurred_at->copy()->addSeconds(5),
                ])
                ->with('symptom')
                ->get();

            $events[] = [
                'type' => 'moment_checkin',
                'date' => $momentCheckin->occurred_at->format('Y-m-d'),
                'time' => $momentCheckin->occurred_at,
                'icon' => $momentCheckin->mood ?? '😐',
                'color' => 'gray',
                'title' => $momentCheckin->mood ?? 'Moment Check-in',
                'message' => $momentCheckin->feeling_level
                    ? "Cảm giác lúc {$momentCheckin->occurred_at->format('H:i')}: {$momentCheckin->feeling_level}/10{$tagsDisplay}"
                    : $tagsDisplay,
                'data' => $momentCheckin,
                'symptoms' => $symptoms,
            ];
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
            // Show symptom_logs as standalone events even if they're in check-ins
            // This allows users to see all symptom_logs clearly in the timeline
            // Users can see symptoms both in check-in events (as tags) and as standalone events
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

    /**
     * Get daily summary for a specific date.
     */
    private function getDailySummary($user, Carbon $date): array
    {
        // Get trend comparison
        $trend = $this->calculateDayTrend($user, $date);

        // Get overall feeling (from DailyCheckin or average of MomentCheckins)
        $dailyCheckin = DailyCheckin::where('user_id', $user->id)
            ->where('checkin_date', $date)
            ->first();

        $overallFeeling = null;
        if ($dailyCheckin && $dailyCheckin->overall_feeling) {
            $overallFeeling = $dailyCheckin->overall_feeling;
        } else {
            // Fallback to average of moment check-ins
            $momentCheckins = MomentCheckin::where('user_id', $user->id)
                ->whereDate('occurred_at', $date)
                ->whereNotNull('feeling_level')
                ->get();

            if ($momentCheckins->count() > 0) {
                $overallFeeling = (int) round($momentCheckins->avg('feeling_level'));
            }
        }

        // Get primary symptoms
        $primarySymptoms = $this->getPrimarySymptoms($user, $date);

        // Get alert count
        $alertCount = Alert::where('user_id', $user->id)
            ->whereDate('triggered_at', $date)
            ->count();

        return [
            'trend' => $trend['direction'],
            'trend_icon' => $trend['icon'],
            'trend_label' => $trend['label'],
            'overall_feeling' => $overallFeeling,
            'primary_symptoms' => $primarySymptoms,
            'alert_count' => $alertCount,
            'day_name' => $date->locale('vi')->translatedFormat('l'),
            'date_formatted' => $date->format('d/m/Y'),
        ];
    }

    /**
     * Calculate trend for a specific day compared to previous day.
     */
    private function calculateDayTrend($user, Carbon $date): array
    {
        $previousDate = $date->copy()->subDay();

        // Calculate composite severity for today
        $maxSeverityToday = SymptomLog::where('user_id', $user->id)
            ->whereDate('occurred_at', $date)
            ->max('severity') ?? 0;

        $checkinToday = DailyCheckin::where('user_id', $user->id)
            ->where('checkin_date', $date)
            ->first();

        $severityToday = $this->calculateCompositeSeverity($maxSeverityToday, $checkinToday);

        // Calculate composite severity for yesterday
        $maxSeverityYesterday = SymptomLog::where('user_id', $user->id)
            ->whereDate('occurred_at', $previousDate)
            ->max('severity') ?? 0;

        $checkinYesterday = DailyCheckin::where('user_id', $user->id)
            ->where('checkin_date', $previousDate)
            ->first();

        $severityYesterday = $this->calculateCompositeSeverity($maxSeverityYesterday, $checkinYesterday);

        // If no previous day data, return stable
        if ($severityYesterday === 5.0 && ! $checkinYesterday && $maxSeverityYesterday === 0) {
            return [
                'direction' => 'stable',
                'icon' => '➖',
                'label' => 'Ổn định',
            ];
        }

        // Compare (lower severity = better)
        $difference = $severityYesterday - $severityToday; // Positive = improving
        $threshold = 0.5;

        if ($difference > $threshold) {
            return [
                'direction' => 'improving',
                'icon' => '⬆️',
                'label' => 'Đang tốt lên',
            ];
        } elseif ($difference < -$threshold) {
            return [
                'direction' => 'worsening',
                'icon' => '⬇️',
                'label' => 'Hơi tệ hơn',
            ];
        }

        return [
            'direction' => 'stable',
            'icon' => '➖',
            'label' => 'Ổn định',
        ];
    }

    /**
     * Get primary symptoms for a specific date (top 2-3 by severity).
     */
    private function getPrimarySymptoms($user, Carbon $date): array
    {
        $symptomLogs = SymptomLog::where('user_id', $user->id)
            ->whereDate('occurred_at', $date)
            ->with('symptom')
            ->get();

        if ($symptomLogs->isEmpty()) {
            return [];
        }

        // Calculate weighted severity (critical symptoms × 2)
        $weightedLogs = $symptomLogs->map(function ($log) {
            $weight = ($log->symptom->is_critical ?? false) ? 2.0 : 1.0;

            return [
                'log' => $log,
                'weighted_severity' => $log->severity * $weight,
            ];
        })->sortByDesc('weighted_severity');

        // Get top 2-3 symptoms
        $topSymptoms = $weightedLogs->take(3)->map(function ($item) {
            $log = $item['log'];

            return [
                'code' => $log->symptom_code,
                'name' => $log->symptom->display_name ?? $log->symptom_code,
                'severity' => $log->severity,
                'is_critical' => $log->symptom->is_critical ?? false,
            ];
        })->values()->all();

        return $topSymptoms;
    }

    /**
     * Group moment check-ins with preview.
     */
    private function groupMomentCheckins(array $momentEvents): array
    {
        if (empty($momentEvents)) {
            return [
                'count' => 0,
                'preview' => [],
                'all' => [],
            ];
        }

        // Sort by time (most recent first)
        usort($momentEvents, function ($a, $b) {
            return $b['time']->timestamp <=> $a['time']->timestamp;
        });

        // Preview is first 2-3 items
        $preview = array_slice($momentEvents, 0, min(3, count($momentEvents)));

        return [
            'count' => count($momentEvents),
            'preview' => $preview,
            'all' => $momentEvents,
        ];
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
