<?php

namespace App\Services;

use App\Models\Insight;
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

    // ============================================
    // Phase 1: Core Infrastructure - New Methods
    // ============================================

    /**
     * Aggregate daily scores (weighted average of symptoms, normalized 0-10).
     *
     * @return array<string, float> Date => daily_score
     */
    public function aggregateDailyScores(User $user, int $days = 7): array
    {
        $startDate = Carbon::today()->subDays($days - 1);
        $endDate = Carbon::today();

        $symptomLogs = SymptomLog::where('user_id', $user->id)
            ->whereBetween('occurred_at', [$startDate, $endDate->endOfDay()])
            ->with('symptom')
            ->get()
            ->groupBy(function ($log) {
                return $log->occurred_at->format('Y-m-d');
            });

        $dailyScores = [];

        foreach ($symptomLogs as $date => $logs) {
            $totalWeightedSeverity = 0;
            $totalWeight = 0;

            foreach ($logs as $log) {
                // Weight by symptom severity scale (normalize to 0-10)
                $symptomScale = $log->symptom->severity_scale ?? 10;
                $normalizedSeverity = ($log->severity / $symptomScale) * 10;
                $weight = $log->symptom->is_critical ? 2.0 : 1.0; // Critical symptoms weighted higher

                $totalWeightedSeverity += $normalizedSeverity * $weight;
                $totalWeight += $weight;
            }

            // Calculate weighted average and ensure it's between 0-10
            $dailyScore = $totalWeight > 0 ? ($totalWeightedSeverity / $totalWeight) : 0;
            $dailyScores[$date] = min(10, max(0, round($dailyScore, 1)));
        }

        // Fill missing dates with 0
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            if (! isset($dailyScores[$date])) {
                $dailyScores[$date] = 0.0;
            }
        }

        ksort($dailyScores);

        return $dailyScores;
    }

    /**
     * Calculate 3-day average from daily scores.
     */
    public function calculate3DayAverage(array $dailyScores): float
    {
        $last3Days = array_slice($dailyScores, -3, 3, true);
        $sum = array_sum($last3Days);
        $count = count($last3Days);

        return $count > 0 ? round($sum / $count, 1) : 0.0;
    }

    /**
     * Calculate 7-day average from daily scores.
     */
    public function calculate7DayAverage(array $dailyScores): float
    {
        $sum = array_sum($dailyScores);
        $count = count($dailyScores);

        return $count > 0 ? round($sum / $count, 1) : 0.0;
    }

    /**
     * Calculate trend using 3d_avg vs 7d_avg (fixed logic per spec).
     */
    public function calculateTrendFixed(User $user): array
    {
        $dailyScores = $this->aggregateDailyScores($user, 7);

        if (count($dailyScores) < 3) {
            return ['direction' => 'stable', 'change' => 0, 'days' => 0];
        }

        $avg3d = $this->calculate3DayAverage($dailyScores);
        $avg7d = $this->calculate7DayAverage($dailyScores);

        $change = $avg3d - $avg7d;
        $threshold = 1.5;

        if ($change >= $threshold) {
            return [
                'direction' => 'worsening',
                'change' => round($change, 1),
                'days' => 3,
                '3d_avg' => $avg3d,
                '7d_avg' => $avg7d,
            ];
        } elseif ($change <= -$threshold) {
            return [
                'direction' => 'improving',
                'change' => round(abs($change), 1),
                'days' => 3,
                '3d_avg' => $avg3d,
                '7d_avg' => $avg7d,
            ];
        }

        return [
            'direction' => 'stable',
            'change' => 0,
            'days' => 3,
            '3d_avg' => $avg3d,
            '7d_avg' => $avg7d,
        ];
    }

    // ============================================
    // Phase 2: Pattern Detection
    // ============================================

    /**
     * Detect night vs day pattern.
     */
    public function detectNightDayPattern(User $user, ?string $symptomCode = null): ?array
    {
        $startDate = Carbon::today()->subDays(6);
        $endDate = Carbon::today();

        $query = SymptomLog::where('user_id', $user->id)
            ->whereBetween('occurred_at', [$startDate, $endDate->endOfDay()]);

        if ($symptomCode) {
            $query->where('symptom_code', $symptomCode);
        }

        $logs = $query->get();

        if ($logs->isEmpty()) {
            return null;
        }

        $nightSeverities = [];
        $daySeverities = [];

        foreach ($logs as $log) {
            $hour = (int) $log->occurred_at->format('H');
            // Night: 20:00 - 08:00 (8 PM to 8 AM)
            if ($hour >= 20 || $hour < 8) {
                $nightSeverities[] = $log->severity;
            } else {
                $daySeverities[] = $log->severity;
            }
        }

        if (empty($nightSeverities) || empty($daySeverities)) {
            return null;
        }

        $nightAvg = array_sum($nightSeverities) / count($nightSeverities);
        $dayAvg = array_sum($daySeverities) / count($daySeverities);
        $difference = $nightAvg - $dayAvg;
        $threshold = 2.0;

        if ($difference >= $threshold) {
            return [
                'pattern' => 'night_worsening',
                'night_avg' => round($nightAvg, 1),
                'day_avg' => round($dayAvg, 1),
                'difference' => round($difference, 1),
            ];
        }

        return null;
    }

    /**
     * Detect weekday vs weekend pattern.
     */
    public function detectWeekdayWeekendPattern(User $user, ?string $symptomCode = null): ?array
    {
        $startDate = Carbon::today()->subDays(6);
        $endDate = Carbon::today();

        $query = SymptomLog::where('user_id', $user->id)
            ->whereBetween('occurred_at', [$startDate, $endDate->endOfDay()]);

        if ($symptomCode) {
            $query->where('symptom_code', $symptomCode);
        }

        $logs = $query->get();

        if ($logs->isEmpty()) {
            return null;
        }

        $weekdaySeverities = [];
        $weekendSeverities = [];

        foreach ($logs as $log) {
            $dayOfWeek = (int) $log->occurred_at->format('w'); // 0 = Sunday, 6 = Saturday
            if ($dayOfWeek == 0 || $dayOfWeek == 6) {
                $weekendSeverities[] = $log->severity;
            } else {
                $weekdaySeverities[] = $log->severity;
            }
        }

        if (empty($weekdaySeverities) || empty($weekendSeverities)) {
            return null;
        }

        $weekdayAvg = array_sum($weekdaySeverities) / count($weekdaySeverities);
        $weekendAvg = array_sum($weekendSeverities) / count($weekendSeverities);
        $difference = abs($weekendAvg - $weekdayAvg);
        $threshold = 1.5;

        if ($difference >= $threshold) {
            return [
                'pattern' => $weekendAvg > $weekdayAvg ? 'weekend_worse' : 'weekday_worse',
                'weekday_avg' => round($weekdayAvg, 1),
                'weekend_avg' => round($weekendAvg, 1),
                'difference' => round($difference, 1),
            ];
        }

        return null;
    }

    /**
     * Detect medication response pattern.
     */
    public function detectMedicationResponse(User $user, ?string $symptomCode = null): ?array
    {
        // Get recent medication logs (last 7 days)
        $startDate = Carbon::today()->subDays(7);
        $medicationLogs = $user->medicationLogs()
            ->where('taken_at', '>=', $startDate)
            ->orderBy('taken_at', 'asc')
            ->get();

        if ($medicationLogs->isEmpty()) {
            return null;
        }

        // Find first medication taken in last 3 days
        $firstMedicationDate = $medicationLogs->first()->taken_at;
        $daysSinceMedication = Carbon::now()->diffInDays($firstMedicationDate);

        if ($daysSinceMedication > 3) {
            return null; // Medication started more than 3 days ago
        }

        // Get symptom logs before and after medication
        $beforeDate = $firstMedicationDate->copy()->subDays(3);
        $afterDate = Carbon::now();

        $query = SymptomLog::where('user_id', $user->id)
            ->whereBetween('occurred_at', [$beforeDate, $afterDate]);

        if ($symptomCode) {
            $query->where('symptom_code', $symptomCode);
        }

        $symptomLogs = $query->orderBy('occurred_at', 'asc')->get();

        if ($symptomLogs->count() < 4) {
            return null; // Not enough data
        }

        // Split into before and after medication
        $beforeMedication = $symptomLogs->filter(function ($log) use ($firstMedicationDate) {
            return $log->occurred_at < $firstMedicationDate;
        });

        $afterMedication = $symptomLogs->filter(function ($log) use ($firstMedicationDate) {
            return $log->occurred_at >= $firstMedicationDate;
        });

        if ($beforeMedication->isEmpty() || $afterMedication->isEmpty()) {
            return null;
        }

        $beforeAvg = $beforeMedication->avg('severity');
        $afterAvg = $afterMedication->avg('severity');
        $change = $beforeAvg - $afterAvg; // Positive = improvement

        if ($change >= 1.0) {
            return [
                'pattern' => 'positive_response',
                'before_avg' => round($beforeAvg, 1),
                'after_avg' => round($afterAvg, 1),
                'improvement' => round($change, 1),
                'days_since' => $daysSinceMedication,
            ];
        } elseif ($change <= -0.5) {
            return [
                'pattern' => 'no_response',
                'before_avg' => round($beforeAvg, 1),
                'after_avg' => round($afterAvg, 1),
                'worsening' => round(abs($change), 1),
                'days_since' => $daysSinceMedication,
            ];
        }

        return null;
    }

    // ============================================
    // Phase 3: Context & Comparison
    // ============================================

    /**
     * Get user context (pregnancy, disease type).
     */
    public function getUserContext(User $user): array
    {
        return [
            'is_pregnant' => $user->isPregnant(),
            'disease_type' => $user->getDiseaseType(),
            'conditions' => $user->conditions ?? [],
        ];
    }

    /**
     * Check if reassurance insights should be disabled.
     */
    public function shouldDisableReassurance(User $user): bool
    {
        return $user->isPregnant();
    }

    /**
     * Filter insights by context (disable inappropriate insights).
     */
    public function filterInsightsByContext(User $user, array $insights): array
    {
        $context = $this->getUserContext($user);
        $filtered = [];

        foreach ($insights as $insight) {
            // Disable reassurance for pregnancy
            if ($insight['type'] === 'REASSURANCE' && $context['is_pregnant']) {
                continue;
            }

            // Disease-specific filtering can be added here
            $filtered[] = $insight;
        }

        return $filtered;
    }

    /**
     * Generate comparison insight (vs last week, vs personal average).
     */
    public function generateComparisonInsight(User $user, ?string $symptomCode = null): ?array
    {
        $dailyScores = $this->aggregateDailyScores($user, 30); // Get 30 days for personal average

        if (count($dailyScores) < 7) {
            return null;
        }

        // Last 7 days average
        $last7Days = array_slice($dailyScores, -7, 7, true);
        $currentAvg = array_sum($last7Days) / count($last7Days);

        // Previous week (days 8-14)
        if (count($dailyScores) >= 14) {
            $previousWeek = array_slice($dailyScores, -14, 7, true);
            $previousAvg = array_sum($previousWeek) / count($previousWeek);
            $vsLastWeek = $currentAvg - $previousAvg;

            if (abs($vsLastWeek) >= 1.0) {
                return [
                    'type' => 'COMPARISON',
                    'baseline' => 'last_week',
                    'current_avg' => round($currentAvg, 1),
                    'baseline_avg' => round($previousAvg, 1),
                    'difference' => round($vsLastWeek, 1),
                    'higher' => $vsLastWeek > 0,
                ];
            }
        }

        // Personal average (last 30 days)
        if (count($dailyScores) >= 14) {
            $personalAvg = array_sum($dailyScores) / count($dailyScores);
            $vsPersonal = $currentAvg - $personalAvg;

            if (abs($vsPersonal) >= 1.0) {
                return [
                    'type' => 'COMPARISON',
                    'baseline' => 'personal_average',
                    'current_avg' => round($currentAvg, 1),
                    'baseline_avg' => round($personalAvg, 1),
                    'difference' => round($vsPersonal, 1),
                    'higher' => $vsPersonal > 0,
                ];
            }
        }

        return null;
    }

    // ============================================
    // Phase 4: Insight Generation Rules
    // ============================================

    /**
     * Generate insights using rules-based system.
     *
     * @return array<int, array<string, mixed>>
     */
    public function generateInsights(User $user): array
    {
        // 1. Aggregate data
        $dailyScores = $this->aggregateDailyScores($user, 7);
        $trend = $this->calculateTrendFixed($user);
        $primarySymptom = $this->getPrimarySymptom($user, $this->getLast7DaysData($user));
        $context = $this->getUserContext($user);

        // 2. Detect patterns
        $patterns = [];
        if ($primarySymptom) {
            $nightDayPattern = $this->detectNightDayPattern($user, $primarySymptom['code']);
            if ($nightDayPattern) {
                $patterns['night_worsening'] = $nightDayPattern;
            }

            $weekdayWeekendPattern = $this->detectWeekdayWeekendPattern($user, $primarySymptom['code']);
            if ($weekdayWeekendPattern) {
                $patterns[$weekdayWeekendPattern['pattern']] = $weekdayWeekendPattern;
            }

            $medicationPattern = $this->detectMedicationResponse($user, $primarySymptom['code']);
            if ($medicationPattern) {
                $patterns[$medicationPattern['pattern']] = $medicationPattern;
            }
        }

        // 3. Generate comparison insight
        $comparison = $this->generateComparisonInsight($user, $primarySymptom['code'] ?? null);

        // 4. Generate insights according to rules
        $insights = [];
        $rules = config('insights.rules', []);

        // Check for critical alerts
        $hasCriticalAlerts = $this->hasCriticalAlerts($user);

        // TREND insights
        if ($trend['direction'] === 'worsening' && $trend['days'] >= 3) {
            $insights[] = $this->createInsightFromRule('INS_TREND_WORSENING', $rules, [
                'trend' => $trend,
                'primary_symptom' => $primarySymptom,
            ]);
        } elseif ($trend['direction'] === 'improving' && $trend['days'] >= 3) {
            $insights[] = $this->createInsightFromRule('INS_TREND_IMPROVING', $rules, [
                'trend' => $trend,
                'primary_symptom' => $primarySymptom,
            ]);
        } elseif ($trend['direction'] === 'stable' && ! $hasCriticalAlerts) {
            $reassurance = $this->generateReassuranceInsight($user, $trend);
            if ($reassurance) {
                $insights[] = $reassurance;
            }
        }

        // PATTERN insights
        if (isset($patterns['night_worsening'])) {
            $insights[] = $this->createInsightFromRule('INS_PATTERN_NIGHT_WORSENING', $rules, [
                'pattern' => $patterns['night_worsening'],
            ]);
        }

        if (isset($patterns['weekend_worse']) || isset($patterns['weekday_worse'])) {
            $insights[] = $this->createInsightFromRule('INS_PATTERN_WEEKEND_DIFFERENCE', $rules, [
                'pattern' => $patterns['weekend_worse'] ?? $patterns['weekday_worse'],
            ]);
        }

        if (isset($patterns['positive_response'])) {
            $insights[] = $this->createInsightFromRule('INS_PATTERN_POSITIVE_RESPONSE', $rules, [
                'pattern' => $patterns['positive_response'],
            ]);
        }

        if (isset($patterns['no_response'])) {
            $insights[] = $this->createInsightFromRule('INS_PATTERN_NO_RESPONSE', $rules, [
                'pattern' => $patterns['no_response'],
            ]);
        }

        // COMPARISON insights
        if ($comparison) {
            $code = $comparison['higher'] ? 'INS_COMPARISON_HIGHER_THAN_AVERAGE' : 'INS_COMPARISON_LOWER_THAN_AVERAGE';
            $insights[] = $this->createInsightFromRule($code, $rules, ['comparison' => $comparison]);
        }

        // Disease-specific insights
        if ($context['disease_type'] === 'AD') {
            $adInsight = $this->generateADInsight($user, $primarySymptom);
            if ($adInsight) {
                $insights[] = $adInsight;
            }
        }

        if ($context['disease_type'] === 'AR') {
            $arInsight = $this->generateARInsight($user, $primarySymptom, $patterns);
            if ($arInsight) {
                $insights[] = $arInsight;
            }
        }

        if ($context['disease_type'] === 'PR') {
            $prInsight = $this->generatePRInsight($user);
            if ($prInsight) {
                $insights[] = $prInsight;
            }
        }

        // 5. Apply context filter
        $insights = $this->filterInsightsByContext($user, $insights);

        // 6. Rank & deduplicate
        $insights = $this->rankAndDeduplicateInsights($user, $insights);

        // 7. Return top 1-2 insights
        return array_slice($insights, 0, 2);
    }

    /**
     * Create insight from rule.
     */
    private function createInsightFromRule(string $code, array $rules, array $data): array
    {
        $rule = $rules[$code] ?? null;

        if (! $rule) {
            return [];
        }

        return [
            'code' => $code,
            'type' => $rule['type'],
            'message' => $rule['then']['message'],
            'priority' => $rule['then']['priority'],
            'metadata' => $data,
            'explanation_data' => $this->buildExplanationData($code, $data),
        ];
    }

    /**
     * Build explanation data for insight.
     */
    private function buildExplanationData(string $code, array $data): array
    {
        $explanation = [
            'rule_code' => $code,
            'data_used' => [],
        ];

        if (isset($data['trend'])) {
            $explanation['data_used']['trend'] = [
                'direction' => $data['trend']['direction'],
                '3d_avg' => $data['trend']['3d_avg'] ?? null,
                '7d_avg' => $data['trend']['7d_avg'] ?? null,
                'days' => $data['trend']['days'] ?? 0,
            ];
        }

        if (isset($data['pattern'])) {
            $explanation['data_used']['pattern'] = $data['pattern'];
        }

        if (isset($data['comparison'])) {
            $explanation['data_used']['comparison'] = [
                'baseline' => $data['comparison']['baseline'],
                'current_avg' => $data['comparison']['current_avg'],
                'baseline_avg' => $data['comparison']['baseline_avg'],
            ];
        }

        return $explanation;
    }

    /**
     * Generate reassurance insight.
     */
    public function generateReassuranceInsight(User $user, array $trend): ?array
    {
        if ($this->shouldDisableReassurance($user)) {
            return null;
        }

        $rules = config('insights.rules', []);
        $rule = $rules['INS_STABLE_REASSURE'] ?? null;

        if (! $rule) {
            return null;
        }

        return [
            'code' => 'INS_STABLE_REASSURE',
            'type' => 'REASSURANCE',
            'message' => $rule['then']['message'],
            'priority' => $rule['then']['priority'],
            'metadata' => ['trend' => $trend],
            'explanation_data' => $this->buildExplanationData('INS_STABLE_REASSURE', ['trend' => $trend]),
        ];
    }

    /**
     * Check if user has critical alerts.
     */
    private function hasCriticalAlerts(User $user): bool
    {
        $startDate = Carbon::today()->subDays(7);

        return $user->alerts()
            ->where('triggered_at', '>=', $startDate)
            ->where('severity', 'critical')
            ->exists();
    }

    // ============================================
    // Phase 5: Ranking & Deduplication
    // ============================================

    /**
     * Rank and deduplicate insights.
     *
     * @param  array<int, array<string, mixed>>  $insights
     * @return array<int, array<string, mixed>>
     */
    public function rankAndDeduplicateInsights(User $user, array $insights): array
    {
        if (empty($insights)) {
            return [];
        }

        // Calculate scores for each insight
        $scoredInsights = [];
        foreach ($insights as $insight) {
            $score = $this->calculateInsightScore($user, $insight);
            $scoredInsights[] = array_merge($insight, ['_score' => $score]);
        }

        // Sort by score (descending)
        usort($scoredInsights, function ($a, $b) {
            return $b['_score'] <=> $a['_score'];
        });

        // Deduplicate (check last 48 hours)
        $deduplicated = [];
        $cutoffTime = Carbon::now()->subHours(48);

        foreach ($scoredInsights as $insight) {
            $isDuplicate = Insight::where('user_id', $user->id)
                ->where('code', $insight['code'])
                ->where('generated_at', '>=', $cutoffTime)
                ->exists();

            if (! $isDuplicate) {
                $deduplicated[] = $insight;
            }
        }

        // Remove score from final output
        return array_map(function ($insight) {
            unset($insight['_score']);

            return $insight;
        }, $deduplicated);
    }

    /**
     * Calculate insight score for ranking.
     */
    private function calculateInsightScore(User $user, array $insight): float
    {
        $score = 0;

        // Priority weight
        $priorityWeights = [
            'high' => 10,
            'medium' => 5,
            'low' => 1,
        ];
        $score += $priorityWeights[$insight['priority']] ?? 0;

        // Trend strength
        if (isset($insight['metadata']['trend'])) {
            $trend = $insight['metadata']['trend'];
            $score += abs($trend['change'] ?? 0) * 2;
        }

        // Context risk (higher for worsening trends)
        if (isset($insight['metadata']['trend']) && $insight['metadata']['trend']['direction'] === 'worsening') {
            $score += 5;
        }

        // Repetition penalty (will be applied in deduplication)
        // New insights get bonus
        $isNew = ! Insight::where('user_id', $user->id)
            ->where('code', $insight['code'])
            ->where('generated_at', '>=', Carbon::today())
            ->exists();

        if ($isNew) {
            $score += 3;
        }

        return $score;
    }

    // ============================================
    // Phase 6: Disease-specific Insights
    // ============================================

    /**
     * Generate Atopic Dermatitis (AD) specific insights.
     */
    public function generateADInsight(User $user, ?array $primarySymptom): ?array
    {
        if (! $primarySymptom) {
            return null;
        }

        $startDate = Carbon::today()->subDays(7);
        $logs = SymptomLog::where('user_id', $user->id)
            ->where('symptom_code', 'itch')
            ->where('occurred_at', '>=', $startDate)
            ->get();

        $sleepLogs = SymptomLog::where('user_id', $user->id)
            ->where('symptom_code', 'sleep_disturbance')
            ->where('occurred_at', '>=', $startDate)
            ->get();

        if ($logs->isNotEmpty() && $sleepLogs->isNotEmpty()) {
            $avgItch = $logs->avg('severity');
            $avgSleep = $sleepLogs->avg('severity');

            if ($avgItch >= 6 && $avgSleep >= 5) {
                $rules = config('insights.rules', []);
                $rule = $rules['INS_AD_SLEEP_IMPACT'] ?? null;

                if ($rule) {
                    return [
                        'code' => 'INS_AD_SLEEP_IMPACT',
                        'type' => 'CONTEXTUAL',
                        'message' => $rule['then']['message'],
                        'priority' => $rule['then']['priority'],
                        'metadata' => [
                            'itch_avg' => round($avgItch, 1),
                            'sleep_avg' => round($avgSleep, 1),
                        ],
                        'explanation_data' => [
                            'rule_code' => 'INS_AD_SLEEP_IMPACT',
                            'data_used' => [
                                'itch_avg' => round($avgItch, 1),
                                'sleep_avg' => round($avgSleep, 1),
                            ],
                        ],
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Generate Allergic Rhinitis (AR) specific insights.
     */
    public function generateARInsight(User $user, ?array $primarySymptom, array $patterns): ?array
    {
        if (isset($patterns['night_worsening'])) {
            $rules = config('insights.rules', []);
            $rule = $rules['INS_AR_NIGHT_WORSENING'] ?? null;

            if ($rule) {
                return [
                    'code' => 'INS_AR_NIGHT_WORSENING',
                    'type' => 'CONTEXTUAL',
                    'message' => $rule['then']['message'],
                    'priority' => $rule['then']['priority'],
                    'metadata' => ['pattern' => $patterns['night_worsening']],
                    'explanation_data' => $this->buildExplanationData('INS_AR_NIGHT_WORSENING', ['pattern' => $patterns['night_worsening']]),
                ];
            }
        }

        return null;
    }

    /**
     * Generate Pregnancy (PR) specific insights.
     */
    public function generatePRInsight(User $user): ?array
    {
        // Conservative, safety-first insights for pregnancy
        $startDate = Carbon::today()->subDays(7);
        $criticalSymptoms = SymptomLog::where('user_id', $user->id)
            ->where('occurred_at', '>=', $startDate)
            ->whereHas('symptom', function ($query) {
                $query->where('is_critical', true);
            })
            ->where('severity', '>=', 1)
            ->exists();

        if ($criticalSymptoms) {
            return [
                'code' => 'INS_PR_CAUTION_NEEDED',
                'type' => 'CONTEXTUAL',
                'message' => 'Bạn đang có triệu chứng cần chú ý trong thai kỳ. Hãy trao đổi với bác sĩ sớm.',
                'priority' => 'high',
                'metadata' => ['context' => 'pregnancy'],
                'explanation_data' => [
                    'rule_code' => 'INS_PR_CAUTION_NEEDED',
                    'data_used' => ['has_critical_symptoms' => true],
                ],
            ];
        }

        return null;
    }
}
