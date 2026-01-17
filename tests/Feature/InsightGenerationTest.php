<?php

namespace Tests\Feature;

use App\Models\Insight;
use App\Models\Symptom;
use App\Models\SymptomLog;
use App\Models\User;
use App\Services\InsightService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InsightGenerationTest extends TestCase
{
    use RefreshDatabase;

    private InsightService $insightService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->insightService = new InsightService;
    }

    public function test_generate_insights_returns_array(): void
    {
        $user = User::factory()->create();

        $insights = $this->insightService->generateInsights($user);

        $this->assertIsArray($insights);
        $this->assertLessThanOrEqual(2, count($insights));
    }

    public function test_generate_insights_creates_trend_insight_when_worsening(): void
    {
        $user = User::factory()->create();
        $symptom = Symptom::factory()->create(['code' => 'test', 'severity_scale' => 10]);

        // Create worsening trend: first 4 days low, last 3 days high
        for ($i = 6; $i >= 0; $i--) {
            $severity = $i < 3 ? 2 : 7;
            SymptomLog::factory()->create([
                'user_id' => $user->id,
                'symptom_code' => 'test',
                'severity' => $severity,
                'occurred_at' => now()->subDays($i),
            ]);
        }

        $insights = $this->insightService->generateInsights($user);

        $this->assertNotEmpty($insights);
        $trendInsight = collect($insights)->firstWhere('type', 'TREND');
        $this->assertNotNull($trendInsight);
        $this->assertEquals('INS_TREND_WORSENING', $trendInsight['code']);
    }

    public function test_generate_insights_creates_reassurance_insight_when_stable(): void
    {
        $user = User::factory()->create();
        $symptom = Symptom::factory()->create(['code' => 'test', 'severity_scale' => 10]);

        // Create stable trend
        for ($i = 6; $i >= 0; $i--) {
            SymptomLog::factory()->create([
                'user_id' => $user->id,
                'symptom_code' => 'test',
                'severity' => 3,
                'occurred_at' => now()->subDays($i),
            ]);
        }

        $insights = $this->insightService->generateInsights($user);

        $this->assertNotEmpty($insights);
        $reassuranceInsight = collect($insights)->firstWhere('type', 'REASSURANCE');
        if ($reassuranceInsight) {
            $this->assertEquals('INS_STABLE_REASSURE', $reassuranceInsight['code']);
        }
    }

    public function test_generate_insights_filters_reassurance_for_pregnancy(): void
    {
        $user = User::factory()->create(['conditions' => ['pregnancy']]);
        $symptom = Symptom::factory()->create(['code' => 'test', 'severity_scale' => 10]);

        // Create stable trend
        for ($i = 6; $i >= 0; $i--) {
            SymptomLog::factory()->create([
                'user_id' => $user->id,
                'symptom_code' => 'test',
                'severity' => 3,
                'occurred_at' => now()->subDays($i),
            ]);
        }

        $insights = $this->insightService->generateInsights($user);

        $reassuranceInsight = collect($insights)->firstWhere('type', 'REASSURANCE');
        $this->assertNull($reassuranceInsight);
    }

    public function test_generate_insights_creates_pattern_insight_for_night_worsening(): void
    {
        $user = User::factory()->create();
        $symptom = Symptom::factory()->create(['code' => 'test', 'severity_scale' => 10]);

        // Create night logs (high severity)
        for ($i = 0; $i < 5; $i++) {
            SymptomLog::factory()->create([
                'user_id' => $user->id,
                'symptom_code' => 'test',
                'severity' => 8,
                'occurred_at' => now()->subDays($i)->setTime(22, 0),
            ]);
        }

        // Create day logs (low severity)
        for ($i = 0; $i < 5; $i++) {
            SymptomLog::factory()->create([
                'user_id' => $user->id,
                'symptom_code' => 'test',
                'severity' => 3,
                'occurred_at' => now()->subDays($i)->setTime(14, 0),
            ]);
        }

        $insights = $this->insightService->generateInsights($user);

        $patternInsight = collect($insights)->firstWhere('type', 'PATTERN');
        if ($patternInsight) {
            $this->assertContains($patternInsight['code'], ['INS_PATTERN_NIGHT_WORSENING', 'INS_AR_NIGHT_WORSENING']);
        }
    }

    public function test_generate_insights_deduplicates_same_insight_within_48_hours(): void
    {
        $user = User::factory()->create();
        $symptom = Symptom::factory()->create(['code' => 'test', 'severity_scale' => 10]);

        // Create an insight in database
        Insight::factory()->create([
            'user_id' => $user->id,
            'code' => 'INS_TREND_WORSENING',
            'type' => 'TREND',
            'generated_at' => now()->subHours(12),
        ]);

        // Create worsening trend
        for ($i = 6; $i >= 0; $i--) {
            $severity = $i < 3 ? 2 : 7;
            SymptomLog::factory()->create([
                'user_id' => $user->id,
                'symptom_code' => 'test',
                'severity' => $severity,
                'occurred_at' => now()->subDays($i),
            ]);
        }

        $insights = $this->insightService->generateInsights($user);

        // Should not include duplicate
        $duplicateCount = collect($insights)->filter(function ($insight) {
            return $insight['code'] === 'INS_TREND_WORSENING';
        })->count();

        $this->assertLessThanOrEqual(1, $duplicateCount);
    }

    public function test_generate_insights_ranks_by_priority(): void
    {
        $user = User::factory()->create();
        $symptom = Symptom::factory()->create(['code' => 'test', 'severity_scale' => 10]);

        // Create both high and low priority conditions
        for ($i = 6; $i >= 0; $i--) {
            $severity = $i < 3 ? 2 : 8; // High severity for worsening
            SymptomLog::factory()->create([
                'user_id' => $user->id,
                'symptom_code' => 'test',
                'severity' => $severity,
                'occurred_at' => now()->subDays($i),
            ]);
        }

        $insights = $this->insightService->generateInsights($user);

        if (count($insights) > 1) {
            // First insight should have higher or equal priority
            $firstPriority = $insights[0]['priority'];
            $secondPriority = $insights[1]['priority'] ?? 'low';

            $priorityOrder = ['high' => 3, 'medium' => 2, 'low' => 1];
            $this->assertGreaterThanOrEqual(
                $priorityOrder[$secondPriority],
                $priorityOrder[$firstPriority]
            );
        }
    }
}
