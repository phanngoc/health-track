<?php

namespace App\Services;

use App\Models\DailyCheckin;
use App\Models\HealthEvent;
use App\Models\Insight;
use App\Models\MomentCheckin;
use App\Models\Symptom;
use App\Models\SymptomLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckInService
{
    public function __construct(
        private TimelineService $timelineService,
        private RuleEngineService $ruleEngineService,
        private InsightService $insightService
    ) {}

    /**
     * Process a daily check-in for a user (baseline well-being, 1 per day).
     *
     * @param  array<string, mixed>  $data
     */
    public function processDailyCheckIn(User $user, array $data): DailyCheckin
    {
        return DB::transaction(function () use ($user, $data) {
            $checkinDate = isset($data['checkin_date'])
                ? Carbon::parse($data['checkin_date'])
                : Carbon::today();

            // Map mood emoji to overall_feeling for backward compatibility
            $overallFeeling = $data['overall_feeling'] ?? null;
            if (! $overallFeeling && isset($data['mood'])) {
                $moodMap = [
                    '🙂' => 7,
                    '😐' => 5,
                    '😴' => 4,
                    '😣' => 3,
                    '😄' => 9,
                ];
                $overallFeeling = $moodMap[$data['mood']] ?? null;
            }

            // Use updateOrCreate to enforce unique constraint (1 per day)
            $checkin = DailyCheckin::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'checkin_date' => $checkinDate,
                ],
                [
                    'mood' => $data['mood'] ?? null,
                    'tags' => $data['tags'] ?? null,
                    'overall_feeling' => $overallFeeling,
                    'sleep_hours' => $data['sleep_hours'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]
            );

            // Process symptoms if provided (from detailed check-in form)
            if (isset($data['symptoms']) && is_array($data['symptoms'])) {
                foreach ($data['symptoms'] as $symptomData) {
                    SymptomLog::create([
                        'user_id' => $user->id,
                        'symptom_code' => $symptomData['code'],
                        'severity' => $symptomData['severity'] ?? 0,
                        'occurred_at' => $symptomData['occurred_at'] ?? Carbon::now(),
                        'source' => 'checkin',
                    ]);
                }
            }

            // BẮT BUỘC: Tạo SymptomLog từ mood (baseline)
            // Đây là data chính của check-in
            if ($overallFeeling) {
                $this->createSymptomLogFromMood($user, $overallFeeling, $checkinDate);
            }

            // BỔ SUNG: Tạo SymptomLog từ tags nếu có
            if (isset($data['tags']) && is_array($data['tags']) && ! empty($data['tags'])) {
                $this->createSymptomLogsFromTags($user, $data['tags'], $checkinDate);
            }

            // Generate insights sau khi đã tạo SymptomLog
            $this->generateAndSaveInsights($user);

            // Create health event
            HealthEvent::create([
                'user_id' => $user->id,
                'event_type' => 'checkin',
                'payload' => $data,
                'occurred_at' => Carbon::now(),
            ]);

            // Add to timeline
            $this->timelineService->addEvent($user, 'checkin', $checkin->id, Carbon::now());

            // Trigger rule engine evaluation
            $this->ruleEngineService->evaluate($user);

            return $checkin;
        });
    }

    /**
     * Process a moment check-in for a user (quick mood tracking, multiple per day).
     *
     * @param  array<string, mixed>  $data
     */
    public function processMomentCheckIn(User $user, array $data): MomentCheckin
    {
        return DB::transaction(function () use ($user, $data) {
            Log::debug('Processing moment check-in', [
                'user_id' => $user->id,
                'data' => $data,
            ]);

            $occurredAt = isset($data['occurred_at'])
                ? Carbon::parse($data['occurred_at'])
                : Carbon::now();

            Log::debug('Parsed occurred_at', [
                'user_id' => $user->id,
                'occurred_at' => $occurredAt->toDateTimeString(),
                'original_value' => $data['occurred_at'] ?? null,
            ]);

            // Map mood to feeling_level if not provided
            $feelingLevel = $data['feeling_level'] ?? null;
            if (! $feelingLevel && isset($data['mood'])) {
                $moodMap = [
                    '😄' => 9,
                    '🙂' => 7,
                    '😐' => 5,
                    '😴' => 4,
                    '😣' => 3,
                ];
                $feelingLevel = $moodMap[$data['mood']] ?? null;
                Log::debug('Mapped mood to feeling_level', [
                    'user_id' => $user->id,
                    'mood' => $data['mood'],
                    'feeling_level' => $feelingLevel,
                ]);
            }

            // Create new moment check-in (no unique constraint, multiple per day allowed)
            $momentCheckin = MomentCheckin::create([
                'user_id' => $user->id,
                'feeling_level' => $feelingLevel,
                'mood' => $data['mood'] ?? null,
                'tags' => $data['tags'] ?? null,
                'occurred_at' => $occurredAt,
            ]);

            Log::debug('Created moment check-in', [
                'user_id' => $user->id,
                'moment_checkin_id' => $momentCheckin->id,
                'feeling_level' => $feelingLevel,
                'mood' => $data['mood'] ?? null,
                'tags' => $data['tags'] ?? null,
            ]);

            // Process symptoms if provided
            if (isset($data['symptoms']) && is_array($data['symptoms'])) {
                Log::debug('Processing symptoms', [
                    'user_id' => $user->id,
                    'symptom_count' => count($data['symptoms']),
                ]);

                foreach ($data['symptoms'] as $symptomData) {
                    $symptomLog = SymptomLog::create([
                        'user_id' => $user->id,
                        'symptom_code' => $symptomData['code'],
                        'severity' => $symptomData['severity'] ?? 0,
                        'occurred_at' => $symptomData['occurred_at'] ?? $occurredAt,
                        'source' => 'moment_checkin',
                    ]);

                    Log::debug('Created symptom log', [
                        'user_id' => $user->id,
                        'symptom_log_id' => $symptomLog->id,
                        'symptom_code' => $symptomData['code'],
                        'severity' => $symptomData['severity'] ?? 0,
                    ]);
                }
            }

            // Add to timeline
            $this->timelineService->addEvent($user, 'moment_checkin', $momentCheckin->id, $occurredAt);

            Log::debug('Added event to timeline', [
                'user_id' => $user->id,
                'moment_checkin_id' => $momentCheckin->id,
                'occurred_at' => $occurredAt->toDateTimeString(),
            ]);

            // Trigger rule engine evaluation after creating symptom logs
            if (isset($data['symptoms']) && is_array($data['symptoms']) && count($data['symptoms']) > 0) {
                $this->ruleEngineService->evaluate($user);
            }

            return $momentCheckin;
        });
    }

    /**
     * Process a daily check-in for a user (backward compatibility).
     *
     * @deprecated Use processDailyCheckIn instead
     *
     * @param  array<string, mixed>  $data
     */
    public function processCheckIn(User $user, array $data): DailyCheckin
    {
        return $this->processDailyCheckIn($user, $data);
    }

    /**
     * Create SymptomLog from mood (BẮT BUỘC - baseline).
     */
    private function createSymptomLogFromMood(User $user, int $overallFeeling, Carbon $checkinDate): void
    {
        // Map overall_feeling (1-10, higher = better) to severity (1-10, higher = worse)
        // Inverse mapping: feeling 9 → severity 1, feeling 3 → severity 8
        $severityMap = [
            9 => 1,  // 😄 rất tốt → severity thấp
            8 => 2,
            7 => 3,  // 🙂 ổn → severity thấp-trung bình
            6 => 4,
            5 => 5,  // 😐 bình thường → severity trung bình
            4 => 6,  // 😴 hơi mệt → severity trung bình-cao
            3 => 8,  // 😣 không khỏe → severity cao
            2 => 9,
            1 => 10,
        ];

        $severity = $severityMap[$overallFeeling] ?? 5;

        // Tạo SymptomLog với symptom_code "general_wellbeing"
        SymptomLog::create([
            'user_id' => $user->id,
            'symptom_code' => 'general_wellbeing',
            'severity' => $severity,
            'occurred_at' => $checkinDate->copy()->startOfDay(),
            'source' => 'checkin',
        ]);
    }

    /**
     * Create SymptomLogs from tags (BỔ SUNG - nếu có).
     */
    private function createSymptomLogsFromTags(User $user, array $tags, Carbon $checkinDate): void
    {
        // Mapping tags to symptom codes
        $tagToSymptomMap = [
            '🤒' => 'fatigue',           // Sức khỏe
            '😴' => 'sleep_disturbance', // Thiếu ngủ (nếu có) hoặc fallback to fatigue
        ];

        foreach ($tags as $tag) {
            // Skip if tag không có trong map
            if (! isset($tagToSymptomMap[$tag])) {
                continue;
            }

            $symptomCode = $tagToSymptomMap[$tag];

            // Verify symptom exists in database
            if (! Symptom::where('code', $symptomCode)->exists()) {
                // Fallback: nếu sleep_disturbance không tồn tại, dùng fatigue
                if ($symptomCode === 'sleep_disturbance') {
                    $symptomCode = 'fatigue';
                } else {
                    continue; // Skip nếu symptom không tồn tại
                }
            }

            // Tạo SymptomLog với severity mặc định 5
            SymptomLog::create([
                'user_id' => $user->id,
                'symptom_code' => $symptomCode,
                'severity' => 5, // Default severity for tags
                'occurred_at' => $checkinDate->copy()->startOfDay(),
                'source' => 'checkin',
            ]);
        }
    }

    /**
     * Generate and save insights to database.
     */
    private function generateAndSaveInsights(User $user): void
    {
        $insights = $this->insightService->generateInsights($user);

        foreach ($insights as $insightData) {
            if (empty($insightData) || ! isset($insightData['code'])) {
                continue;
            }

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
    }
}
