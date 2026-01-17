<?php

namespace App\Services;

use App\Models\DailyCheckin;
use App\Models\HealthEvent;
use App\Models\SymptomLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CheckInService
{
    public function __construct(
        private TimelineService $timelineService,
        private RuleEngineService $ruleEngineService
    ) {
    }

    /**
     * Process a daily check-in for a user.
     *
     * @param  array<string, mixed>  $data
     */
    public function processCheckIn(User $user, array $data): DailyCheckin
    {
        return DB::transaction(function () use ($user, $data) {
            $checkinDate = $data['checkin_date'] ?? Carbon::today();

            $checkin = DailyCheckin::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'checkin_date' => $checkinDate,
                ],
                [
                    'overall_feeling' => $data['overall_feeling'] ?? null,
                    'sleep_hours' => $data['sleep_hours'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]
            );

            // Process symptoms if provided
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
}
