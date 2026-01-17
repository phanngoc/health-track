<?php

namespace App\Services;

use App\Models\SymptomLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InsightService
{
    /**
     * Generate daily insight for user.
     */
    public function generateInsight(User $user): array
    {
        $last7Days = $this->getLast7DaysData($user);

        // Find primary symptom (most frequent or highest severity)
        $primarySymptom = $this->getPrimarySymptom($user, $last7Days);

        // Calculate trend
        $trend = $this->calculateTrend($user, $primarySymptom, $last7Days);

        // Generate insight message
        $insight = $this->generateInsightMessage($primarySymptom, $trend, $last7Days);

        // Determine status
        $status = $this->determineStatus($trend, $last7Days);

        return [
            'status' => $status,
            'primary_symptom' => $primarySymptom,
            'trend' => $trend,
            'insight' => $insight,
        ];
    }

    /**
     * Get last 7 days data.
     */
    private function getLast7DaysData(User $user): Collection
    {
        $startDate = Carbon::today()->subDays(6);
        $endDate = Carbon::today();

        return SymptomLog::where('user_id', $user->id)
            ->whereBetween('occurred_at', [$startDate, $endDate->endOfDay()])
            ->with('symptom')
            ->get()
            ->groupBy(function ($log) {
                return $log->occurred_at->format('Y-m-d');
            });
    }

    /**
     * Get primary symptom (most frequent or highest severity).
     */
    private function getPrimarySymptom(User $user, Collection $last7Days): ?array
    {
        if ($last7Days->isEmpty()) {
            return null;
        }

        // Count symptoms by frequency and average severity
        $symptomStats = [];
        foreach ($last7Days->flatten() as $log) {
            $code = $log->symptom_code;
            if (! isset($symptomStats[$code])) {
                $symptomStats[$code] = [
                    'code' => $code,
                    'name' => $log->symptom->display_name ?? $code,
                    'count' => 0,
                    'total_severity' => 0,
                    'max_severity' => 0,
                ];
            }
            $symptomStats[$code]['count']++;
            $symptomStats[$code]['total_severity'] += $log->severity;
            $symptomStats[$code]['max_severity'] = max($symptomStats[$code]['max_severity'], $log->severity);
        }

        if (empty($symptomStats)) {
            return null;
        }

        // Sort by frequency and severity
        usort($symptomStats, function ($a, $b) {
            $scoreA = $a['count'] * 2 + $a['max_severity'];
            $scoreB = $b['count'] * 2 + $b['max_severity'];
            return $scoreB <=> $scoreA;
        });

        $primary = $symptomStats[0];
        $primary['avg_severity'] = round($primary['total_severity'] / $primary['count'], 1);

        return $primary;
    }

    /**
     * Calculate trend (increasing, decreasing, stable).
     */
    private function calculateTrend(User $user, ?array $primarySymptom, Collection $last7Days): array
    {
        if (! $primarySymptom) {
            return ['direction' => 'stable', 'change' => 0, 'days' => 0];
        }

        $symptomCode = $primarySymptom['code'];
        $recentDays = $last7Days->flatten()
            ->where('symptom_code', $symptomCode)
            ->sortBy('occurred_at')
            ->values();

        if ($recentDays->count() < 2) {
            return ['direction' => 'stable', 'change' => 0, 'days' => 0];
        }

        // Get first and last 3 days average
        $first3 = $recentDays->take(3);
        $last3 = $recentDays->take(-3);

        $firstAvg = $first3->avg('severity') ?? 0;
        $lastAvg = $last3->avg('severity') ?? 0;

        $change = $lastAvg - $firstAvg;
        $days = min(3, $recentDays->count());

        if ($change > 1) {
            return ['direction' => 'increasing', 'change' => round($change, 1), 'days' => $days];
        } elseif ($change < -1) {
            return ['direction' => 'decreasing', 'change' => round(abs($change), 1), 'days' => $days];
        }

        return ['direction' => 'stable', 'change' => 0, 'days' => $days];
    }

    /**
     * Generate insight message.
     */
    private function generateInsightMessage(?array $primarySymptom, array $trend, Collection $last7Days): string
    {
        if (! $primarySymptom) {
            return 'Bạn đang trong tình trạng ổn định. Tiếp tục theo dõi sức khỏe hàng ngày.';
        }

        $symptomName = $primarySymptom['name'];
        $days = $trend['days'];

        if ($trend['direction'] === 'increasing') {
            return sprintf(
                'Triệu chứng %s của bạn tăng dần trong %d ngày gần đây.',
                $symptomName,
                $days
            );
        } elseif ($trend['direction'] === 'decreasing') {
            return sprintf(
                'Triệu chứng %s của bạn đang cải thiện trong %d ngày gần đây.',
                $symptomName,
                $days
            );
        }

        return sprintf(
            'Triệu chứng %s của bạn đang ổn định.',
            $symptomName
        );
    }

    /**
     * Determine status (good, watch, warning, critical).
     */
    private function determineStatus(array $trend, Collection $last7Days): array
    {
        if ($last7Days->isEmpty()) {
            return ['badge' => '🟢', 'label' => 'ỔN ĐỊNH', 'color' => 'green'];
        }

        $maxSeverity = $last7Days->flatten()->max('severity') ?? 0;
        $hasRecentAlerts = $last7Days->flatten()->where('severity', '>=', 7)->count() > 0;

        if ($hasRecentAlerts || $maxSeverity >= 8) {
            return ['badge' => '🔴', 'label' => 'CẦN CHÚ Ý', 'color' => 'red'];
        } elseif ($maxSeverity >= 6 || $trend['direction'] === 'increasing') {
            return ['badge' => '🟠', 'label' => 'THEO DÕI', 'color' => 'orange'];
        } elseif ($maxSeverity >= 4) {
            return ['badge' => '🟡', 'label' => 'ĐANG THEO DÕI', 'color' => 'yellow'];
        }

        return ['badge' => '🟢', 'label' => 'ỔN ĐỊNH', 'color' => 'green'];
    }

    /**
     * Get trend arrow.
     */
    public function getTrendArrow(string $direction): string
    {
        return match ($direction) {
            'increasing' => '↗',
            'decreasing' => '↘',
            default => '→',
        };
    }
}
