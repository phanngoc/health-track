<?php

namespace Tests\Unit;

use App\Models\Symptom;
use App\Models\SymptomLog;
use App\Models\User;
use App\Services\InsightService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InsightServiceTest extends TestCase
{
    use RefreshDatabase;

    private InsightService $insightService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->insightService = new InsightService;
    }

    public function test_aggregate_daily_scores_calculates_weighted_average(): void
    {
        $user = User::factory()->create();
        $symptom = Symptom::factory()->create([
            'code' => 'test_symptom',
            'severity_scale' => 10,
            'is_critical' => false,
        ]);

        // Create symptom logs for last 3 days
        SymptomLog::factory()->create([
            'user_id' => $user->id,
            'symptom_code' => 'test_symptom',
            'severity' => 5,
            'occurred_at' => now()->subDays(2),
        ]);

        SymptomLog::factory()->create([
            'user_id' => $user->id,
            'symptom_code' => 'test_symptom',
            'severity' => 7,
            'occurred_at' => now()->subDays(1),
        ]);

        $scores = $this->insightService->aggregateDailyScores($user, 3);

        $this->assertIsArray($scores);
        $this->assertCount(3, $scores);
        $this->assertArrayHasKey(now()->subDays(2)->format('Y-m-d'), $scores);
    }

    public function test_calculate_3_day_average_returns_correct_value(): void
    {
        $scores = [
            '2026-01-15' => 5.0,
            '2026-01-16' => 7.0,
            '2026-01-17' => 6.0,
        ];

        $avg = $this->insightService->calculate3DayAverage($scores);

        $this->assertEquals(6.0, $avg);
    }

    public function test_calculate_7_day_average_returns_correct_value(): void
    {
        $scores = [
            '2026-01-11' => 4.0,
            '2026-01-12' => 5.0,
            '2026-01-13' => 6.0,
            '2026-01-14' => 5.0,
            '2026-01-15' => 7.0,
            '2026-01-16' => 6.0,
            '2026-01-17' => 5.0,
        ];

        $avg = $this->insightService->calculate7DayAverage($scores);

        $this->assertEquals(5.43, round($avg, 2));
    }

    public function test_calculate_trend_fixed_detects_worsening(): void
    {
        $user = User::factory()->create();
        $symptom = Symptom::factory()->create(['code' => 'test', 'severity_scale' => 10]);

        // Create logs: first 4 days low, last 3 days high
        for ($i = 6; $i >= 0; $i--) {
            $severity = $i < 3 ? 2 : 6; // Last 3 days higher
            SymptomLog::factory()->create([
                'user_id' => $user->id,
                'symptom_code' => 'test',
                'severity' => $severity,
                'occurred_at' => now()->subDays($i),
            ]);
        }

        $trend = $this->insightService->calculateTrendFixed($user);

        $this->assertEquals('worsening', $trend['direction']);
        $this->assertGreaterThanOrEqual(1.5, $trend['change']);
    }

    public function test_detect_night_day_pattern_returns_pattern_when_difference_sufficient(): void
    {
        $user = User::factory()->create();
        $symptom = Symptom::factory()->create(['code' => 'test', 'severity_scale' => 10]);

        // Create night logs (high severity)
        for ($i = 0; $i < 5; $i++) {
            SymptomLog::factory()->create([
                'user_id' => $user->id,
                'symptom_code' => 'test',
                'severity' => 8,
                'occurred_at' => now()->subDays($i)->setTime(22, 0), // 10 PM
            ]);
        }

        // Create day logs (low severity)
        for ($i = 0; $i < 5; $i++) {
            SymptomLog::factory()->create([
                'user_id' => $user->id,
                'symptom_code' => 'test',
                'severity' => 3,
                'occurred_at' => now()->subDays($i)->setTime(14, 0), // 2 PM
            ]);
        }

        $pattern = $this->insightService->detectNightDayPattern($user, 'test');

        $this->assertNotNull($pattern);
        $this->assertEquals('night_worsening', $pattern['pattern']);
        $this->assertGreaterThanOrEqual(2.0, $pattern['difference']);
    }

    public function test_get_user_context_returns_correct_values(): void
    {
        $user = User::factory()->create(['conditions' => ['pregnancy']]);

        $context = $this->insightService->getUserContext($user);

        $this->assertTrue($context['is_pregnant']);
        $this->assertEquals('PR', $context['disease_type']);
    }

    public function test_should_disable_reassurance_returns_true_for_pregnancy(): void
    {
        $user = User::factory()->create(['conditions' => ['pregnancy']]);

        $result = $this->insightService->shouldDisableReassurance($user);

        $this->assertTrue($result);
    }
}
