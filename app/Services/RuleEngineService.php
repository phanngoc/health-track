<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\AlertRule;
use App\Models\SymptomLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class RuleEngineService
{
    public function __construct(
        private NotificationService $notificationService,
        private TimelineService $timelineService
    ) {}

    /**
     * Evaluate all active rules for a user.
     */
    public function evaluate(User $user): void
    {
        $rules = AlertRule::where('is_active', true)->get();

        foreach ($rules as $rule) {
            if ($this->shouldTrigger($user, $rule)) {
                $this->triggerAlert($user, $rule);
            }
        }
    }

    /**
     * Check if a rule should trigger for a user.
     */
    private function shouldTrigger(User $user, AlertRule $rule): bool
    {
        // Check cooldown
        $cooldownKey = "alert:cooldown:{$user->id}:{$rule->code}";
        if (Cache::has($cooldownKey)) {
            return false;
        }

        $condition = $rule->condition;

        // Match rule codes by prefix for flexibility
        if (str_starts_with($rule->code, 'symptom_duration_exceeded')) {
            return $this->checkSymptomDuration($user, $condition);
        }

        if (str_starts_with($rule->code, 'severity_increasing')) {
            return $this->checkSeverityIncreasing($user, $condition);
        }

        if ($rule->code === 'missing_checkin_with_symptom') {
            return $this->checkMissingCheckin($user, $condition);
        }

        if (str_starts_with($rule->code, 'symptom_combination_warning') || str_starts_with($rule->code, 'symptom_combination_critical')) {
            return $this->checkSymptomCombination($user, $condition);
        }

        if (str_starts_with($rule->code, 'new_symptom_with_condition')) {
            return $this->checkNewSymptomWithCondition($user, $condition);
        }

        if ($rule->code === 'medication_non_compliance') {
            return $this->checkMedicationNonCompliance($user, $condition);
        }

        if (str_starts_with($rule->code, 'medication_side_effect')) {
            return $this->checkMedicationSideEffect($user, $condition);
        }

        if (str_starts_with($rule->code, 'treatment_no_improvement')) {
            return $this->checkTreatmentNoImprovement($user, $condition);
        }

        if ($rule->code === 'post_vaccine_abnormal') {
            return $this->checkPostVaccineAbnormal($user, $condition);
        }

        // Disease-specific rules
        if (str_starts_with($rule->code, 'AR_') || str_starts_with($rule->code, 'AD_') || str_starts_with($rule->code, 'PR_')) {
            return $this->checkDiseaseSpecificRule($user, $rule, $condition);
        }

        return false;
    }

    /**
     * A1: Symptom duration exceeded.
     */
    private function checkSymptomDuration(User $user, array $condition): bool
    {
        $symptomCode = $condition['symptom'] ?? null;
        $minSeverity = $condition['min_severity'] ?? 4;
        $durationDays = $condition['duration_days'] ?? 5;

        if (! $symptomCode) {
            return false;
        }

        $cutoffDate = Carbon::now()->subDays($durationDays);

        $logs = SymptomLog::where('user_id', $user->id)
            ->where('symptom_code', $symptomCode)
            ->where('severity', '>=', $minSeverity)
            ->where('occurred_at', '>=', $cutoffDate)
            ->get();

        return $logs->count() >= $durationDays;
    }

    /**
     * A2: Severity increasing.
     */
    private function checkSeverityIncreasing(User $user, array $condition): bool
    {
        $symptomCode = $condition['symptom'] ?? null;
        $increaseThreshold = $condition['increase_threshold'] ?? 3;
        $daysBack = $condition['days_back'] ?? 3;

        if (! $symptomCode) {
            return false;
        }

        $today = Carbon::today();
        $daysAgo = $today->copy()->subDays($daysBack);

        $recentLogs = SymptomLog::where('user_id', $user->id)
            ->where('symptom_code', $symptomCode)
            ->where('occurred_at', '>=', $daysAgo)
            ->orderBy('occurred_at', 'asc')
            ->get();

        if ($recentLogs->count() < 2) {
            return false;
        }

        $firstSeverity = $recentLogs->first()->severity;
        $lastSeverity = $recentLogs->last()->severity;

        return ($lastSeverity - $firstSeverity) >= $increaseThreshold;
    }

    /**
     * A3: Missing check-in with symptom.
     */
    private function checkMissingCheckin(User $user, array $condition): bool
    {
        $missingDays = $condition['missing_days'] ?? 2;
        $minSeverity = $condition['min_severity'] ?? 5;

        $lastCheckin = $user->dailyCheckins()->latest('checkin_date')->first();

        if (! $lastCheckin) {
            return false;
        }

        $daysSinceCheckin = Carbon::parse($lastCheckin->checkin_date)->diffInDays(Carbon::today());

        if ($daysSinceCheckin < $missingDays) {
            return false;
        }

        $lastSymptomLog = SymptomLog::where('user_id', $user->id)
            ->latest('occurred_at')
            ->first();

        return $lastSymptomLog && $lastSymptomLog->severity >= $minSeverity;
    }

    /**
     * B1/B2: Symptom combination.
     */
    private function checkSymptomCombination(User $user, array $condition): bool
    {
        $symptoms = $condition['symptoms'] ?? [];

        if (empty($symptoms)) {
            return false;
        }

        $today = Carbon::today();
        $allPresent = true;

        foreach ($symptoms as $symptomConfig) {
            $code = $symptomConfig['code'] ?? null;
            $minSeverity = $symptomConfig['min_severity'] ?? 0;

            if (! $code) {
                continue;
            }

            $hasSymptom = SymptomLog::where('user_id', $user->id)
                ->where('symptom_code', $code)
                ->where('severity', '>=', $minSeverity)
                ->whereDate('occurred_at', $today)
                ->exists();

            if (! $hasSymptom) {
                $allPresent = false;
                break;
            }
        }

        return $allPresent;
    }

    /**
     * B3: New symptom with condition.
     */
    private function checkNewSymptomWithCondition(User $user, array $condition): bool
    {
        $symptomCode = $condition['symptom'] ?? null;
        $requiredConditions = $condition['conditions'] ?? [];

        if (! $symptomCode || empty($requiredConditions)) {
            return false;
        }

        $userConditions = $user->conditions ?? [];

        $hasRequiredCondition = ! empty(array_intersect($requiredConditions, $userConditions));

        if (! $hasRequiredCondition) {
            return false;
        }

        $today = Carbon::today();
        $hasNewSymptom = SymptomLog::where('user_id', $user->id)
            ->where('symptom_code', $symptomCode)
            ->whereDate('occurred_at', $today)
            ->exists();

        return $hasNewSymptom;
    }

    /**
     * C1: Medication non-compliance.
     */
    private function checkMedicationNonCompliance(User $user, array $condition): bool
    {
        $missedDays = $condition['missed_days'] ?? 2;

        $missedCount = $user->medicationLogs()
            ->where('missed', true)
            ->where('taken_at', '>=', Carbon::now()->subDays($missedDays))
            ->count();

        return $missedCount >= $missedDays;
    }

    /**
     * C2: Medication side effect.
     */
    private function checkMedicationSideEffect(User $user, array $condition): bool
    {
        $medicationType = $condition['medication_type'] ?? null;
        $sideEffectSymptom = $condition['side_effect_symptom'] ?? null;

        if (! $medicationType || ! $sideEffectSymptom) {
            return false;
        }

        $recentMedication = $user->medicationLogs()
            ->whereHas('medication', function ($query) use ($medicationType) {
                $query->where('type', $medicationType);
            })
            ->where('taken_at', '>=', Carbon::now()->subDays(7))
            ->exists();

        if (! $recentMedication) {
            return false;
        }

        $hasSideEffect = SymptomLog::where('user_id', $user->id)
            ->where('symptom_code', $sideEffectSymptom)
            ->where('occurred_at', '>=', Carbon::now()->subDays(7))
            ->exists();

        return $hasSideEffect;
    }

    /**
     * C3: Treatment no improvement.
     */
    private function checkTreatmentNoImprovement(User $user, array $condition): bool
    {
        $daysSinceStart = $condition['days_since_start'] ?? 5;
        $symptomCode = $condition['symptom'] ?? null;

        if (! $symptomCode) {
            return false;
        }

        $cutoffDate = Carbon::now()->subDays($daysSinceStart);

        $oldestLog = SymptomLog::where('user_id', $user->id)
            ->where('symptom_code', $symptomCode)
            ->where('occurred_at', '>=', $cutoffDate)
            ->orderBy('occurred_at', 'asc')
            ->first();

        $newestLog = SymptomLog::where('user_id', $user->id)
            ->where('symptom_code', $symptomCode)
            ->where('occurred_at', '>=', $cutoffDate)
            ->orderBy('occurred_at', 'desc')
            ->first();

        if (! $oldestLog || ! $newestLog) {
            return false;
        }

        return $newestLog->severity >= $oldestLog->severity;
    }

    /**
     * D1: Post-vaccine normal.
     */
    private function checkPostVaccineNormal(User $user, array $condition): bool
    {
        $hoursSinceVaccine = $condition['hours_since_vaccine'] ?? 48;
        $maxFever = $condition['max_fever'] ?? 38.5;

        // This is an INFO alert, so it's always true if conditions are met
        return true;
    }

    /**
     * D2: Post-vaccine abnormal.
     */
    private function checkPostVaccineAbnormal(User $user, array $condition): bool
    {
        $hoursSinceVaccine = $condition['hours_since_vaccine'] ?? 72;
        $minFever = $condition['min_fever'] ?? 38;

        // Check for fever symptom after vaccine
        $hasFever = SymptomLog::where('user_id', $user->id)
            ->where('symptom_code', 'fever')
            ->where('severity', '>=', $minFever * 10) // Convert to 0-10 scale
            ->where('occurred_at', '>=', Carbon::now()->subHours($hoursSinceVaccine))
            ->exists();

        return $hasFever;
    }

    /**
     * Check disease-specific rules (AR, AD, PR).
     */
    private function checkDiseaseSpecificRule(User $user, AlertRule $rule, array $condition): bool
    {
        // AR-01, AD-04: Symptom duration (already handled by checkSymptomDuration)
        if ($rule->code === 'AR_01_PERSISTENT_CONGESTION' || $rule->code === 'AD_04_NO_IMPROVEMENT') {
            return $this->checkSymptomDuration($user, $condition);
        }

        // AR-02, AD-01, AD-02: Symptom combination
        if (in_array($rule->code, ['AR_02_CLASSIC_ALLERGY_PATTERN', 'AD_01_ACUTE_FLARE', 'AD_02_SLEEP_IMPACT'])) {
            return $this->checkSymptomCombination($user, $condition);
        }

        // AR-03: Sinus complication (symptom combination with duration)
        if ($rule->code === 'AR_03_SINUS_COMPLICATION') {
            $hasCombination = $this->checkSymptomCombination($user, $condition);
            if (! $hasCombination) {
                return false;
            }

            $durationDays = $condition['duration_days'] ?? 3;
            $cutoffDate = Carbon::now()->subDays($durationDays);

            $hasNasalCongestion = SymptomLog::where('user_id', $user->id)
                ->where('symptom_code', 'nasal_congestion')
                ->where('severity', '>=', 6)
                ->where('occurred_at', '>=', $cutoffDate)
                ->exists();

            $hasHeadache = SymptomLog::where('user_id', $user->id)
                ->where('symptom_code', 'headache')
                ->where('severity', '>=', 6)
                ->where('occurred_at', '>=', $cutoffDate)
                ->exists();

            return $hasNasalCongestion && $hasHeadache;
        }

        // AD-03: Infection risk (OR condition)
        if ($rule->code === 'AD_03_INFECTION_RISK') {
            $symptoms = $condition['symptoms'] ?? [];
            $anyMatch = $condition['any_match'] ?? false;

            if (empty($symptoms)) {
                return false;
            }

            $today = Carbon::today();

            foreach ($symptoms as $symptomConfig) {
                $code = $symptomConfig['code'] ?? null;
                $minSeverity = $symptomConfig['min_severity'] ?? 0;

                if (! $code) {
                    continue;
                }

                $hasSymptom = SymptomLog::where('user_id', $user->id)
                    ->where('symptom_code', $code)
                    ->where('severity', '>=', $minSeverity)
                    ->whereDate('occurred_at', $today)
                    ->exists();

                if ($hasSymptom && $anyMatch) {
                    return true; // OR: any match is enough
                }

                if (! $hasSymptom && ! $anyMatch) {
                    return false; // AND: all must match
                }
            }

            // If any_match is true and we got here, no symptoms matched
            // If any_match is false and we got here, all symptoms matched
            return ! $anyMatch;
        }

        // PR-01: Vaginal bleeding
        if ($rule->code === 'PR_01_BLEEDING') {
            $symptomCode = $condition['symptom'] ?? null;
            $minSeverity = $condition['min_severity'] ?? 1;

            if (! $symptomCode) {
                return false;
            }

            $today = Carbon::today();

            return SymptomLog::where('user_id', $user->id)
                ->where('symptom_code', $symptomCode)
                ->where('severity', '>=', $minSeverity)
                ->whereDate('occurred_at', $today)
                ->exists();
        }

        // PR-02: Preeclampsia (symptom combination)
        if ($rule->code === 'PR_02_PREECLAMPSIA') {
            return $this->checkSymptomCombination($user, $condition);
        }

        // PR-03: Persistent pain (duration in hours)
        if ($rule->code === 'PR_03_PERSISTENT_PAIN') {
            $symptomCode = $condition['symptom'] ?? null;
            $minSeverity = $condition['min_severity'] ?? 5;
            $durationHours = $condition['duration_hours'] ?? 6;

            if (! $symptomCode) {
                return false;
            }

            $cutoffTime = Carbon::now()->subHours($durationHours);

            $logs = SymptomLog::where('user_id', $user->id)
                ->where('symptom_code', $symptomCode)
                ->where('severity', '>=', $minSeverity)
                ->where('occurred_at', '>=', $cutoffTime)
                ->get();

            return $logs->count() > 0;
        }

        // PR-04: Reduced fetal movement
        if ($rule->code === 'PR_04_REDUCED_FETAL_MOVEMENT') {
            $symptomCode = $condition['symptom'] ?? null;
            $maxSeverity = $condition['max_severity'] ?? 0;

            if (! $symptomCode) {
                return false;
            }

            $today = Carbon::today();

            return SymptomLog::where('user_id', $user->id)
                ->where('symptom_code', $symptomCode)
                ->where('severity', '<=', $maxSeverity)
                ->whereDate('occurred_at', $today)
                ->exists();
        }

        return false;
    }

    /**
     * Trigger an alert for a user.
     */
    private function triggerAlert(User $user, AlertRule $rule): void
    {
        $message = $this->generateAlertMessage($user, $rule);

        $alert = Alert::create([
            'user_id' => $user->id,
            'rule_code' => $rule->code,
            'severity' => $rule->severity,
            'message' => $message,
            'triggered_at' => Carbon::now(),
        ]);

        // Set cooldown
        $cooldownKey = "alert:cooldown:{$user->id}:{$rule->code}";
        Cache::put($cooldownKey, true, now()->addHours($rule->cooldown_hours));

        // Add to timeline
        $this->timelineService->addEvent($user, 'alert', $alert->id, Carbon::now());

        // Send notification
        $this->notificationService->sendAlert($user, $alert);
    }

    /**
     * Generate alert message based on rule and user data.
     */
    private function generateAlertMessage(User $user, AlertRule $rule): string
    {
        $condition = $rule->condition;

        if (str_starts_with($rule->code, 'symptom_duration_exceeded')) {
            return $this->generateSymptomDurationMessage($user, $condition);
        }

        if (str_starts_with($rule->code, 'severity_increasing')) {
            return $this->generateSeverityIncreasingMessage($user, $condition);
        }

        if ($rule->code === 'missing_checkin_with_symptom') {
            return $this->generateMissingCheckinMessage($user, $condition);
        }

        if (str_starts_with($rule->code, 'symptom_combination_warning') || str_starts_with($rule->code, 'symptom_combination_critical')) {
            return $this->generateSymptomCombinationMessage($user, $condition);
        }

        if (str_starts_with($rule->code, 'new_symptom_with_condition')) {
            return $this->generateNewSymptomMessage($user, $condition);
        }

        if ($rule->code === 'medication_non_compliance') {
            return $this->generateMedicationNonComplianceMessage($user, $condition);
        }

        if (str_starts_with($rule->code, 'medication_side_effect')) {
            return $this->generateMedicationSideEffectMessage($user, $condition);
        }

        if (str_starts_with($rule->code, 'treatment_no_improvement')) {
            return $this->generateTreatmentNoImprovementMessage($user, $condition);
        }

        if ($rule->code === 'post_vaccine_abnormal') {
            return $this->generatePostVaccineAbnormalMessage($user, $condition);
        }

        // Disease-specific messages
        if (str_starts_with($rule->code, 'AR_') || str_starts_with($rule->code, 'AD_') || str_starts_with($rule->code, 'PR_')) {
            return $this->generateDiseaseSpecificMessage($rule);
        }

        return "Cảnh báo: {$rule->code}";
    }

    /**
     * Generate messages for disease-specific rules.
     */
    private function generateDiseaseSpecificMessage(AlertRule $rule): string
    {
        $messages = [
            'AR_01_PERSISTENT_CONGESTION' => 'Nghẹt mũi của bạn kéo dài nhiều ngày liên tiếp. Viêm mũi dị ứng có thể đang tiến triển nặng hơn. Bạn nên cân nhắc đi khám tai–mũi–họng.',
            'AR_02_CLASSIC_ALLERGY_PATTERN' => 'Các triệu chứng của bạn phù hợp với đợt bùng phát viêm mũi dị ứng. Hãy theo dõi sát trong 24–48 giờ tới.',
            'AR_03_SINUS_COMPLICATION' => 'Nghẹt mũi kèm đau đầu kéo dài có thể liên quan đến viêm xoang. Bạn nên đi khám để được kiểm tra kỹ hơn.',
            'AD_01_ACUTE_FLARE' => 'Da của bạn đang có dấu hiệu bùng phát viêm da cơ địa. Tránh gãi và cân nhắc đi khám da liễu nếu không cải thiện.',
            'AD_02_SLEEP_IMPACT' => 'Ngứa da đang ảnh hưởng đến giấc ngủ của bạn, đây là dấu hiệu bệnh có xu hướng nặng hơn.',
            'AD_03_INFECTION_RISK' => 'Vùng da có dấu hiệu rỉ dịch hoặc nứt nẻ. Bạn nên đi khám da liễu sớm để tránh nhiễm trùng.',
            'AD_04_NO_IMPROVEMENT' => 'Triệu chứng viêm da kéo dài nhiều ngày. Việc theo dõi và điều chỉnh điều trị là cần thiết.',
            'PR_01_BLEEDING' => 'Bạn có dấu hiệu ra máu trong thai kỳ. Hãy đến cơ sở y tế hoặc liên hệ bác sĩ ngay lập tức.',
            'PR_02_PREECLAMPSIA' => 'Các triệu chứng của bạn có thể liên quan đến tiền sản giật. Đây là tình trạng nguy hiểm cần được kiểm tra ngay.',
            'PR_03_PERSISTENT_PAIN' => 'Đau bụng kéo dài trong thai kỳ cần được theo dõi kỹ. Bạn nên liên hệ bác sĩ để được tư vấn.',
            'PR_04_REDUCED_FETAL_MOVEMENT' => 'Thai máy giảm là dấu hiệu cần được kiểm tra sớm. Hãy đến bệnh viện hoặc gọi bác sĩ ngay.',
        ];

        return $messages[$rule->code] ?? "Cảnh báo: {$rule->code}";
    }

    private function generateSymptomDurationMessage(User $user, array $condition): string
    {
        $symptomCode = $condition['symptom'] ?? null;
        $durationDays = $condition['duration_days'] ?? 5;
        $symptomName = $this->getSymptomDisplayName($symptomCode);

        return "Triệu chứng {$symptomName} của bạn đã kéo dài {$durationDays} ngày. Bạn nên cân nhắc đi khám để được kiểm tra kỹ hơn.";
    }

    private function generateSeverityIncreasingMessage(User $user, array $condition): string
    {
        $symptomCode = $condition['symptom'] ?? null;
        $symptomName = $this->getSymptomDisplayName($symptomCode);

        return "Triệu chứng {$symptomName} của bạn đang tăng dần. Bạn nên theo dõi sát và cân nhắc đi khám nếu tình trạng tiếp tục xấu đi.";
    }

    private function generateMissingCheckinMessage(User $user, array $condition): string
    {
        $missingDays = $condition['missing_days'] ?? 2;
        $lastCheckin = $user->dailyCheckins()->latest('checkin_date')->first();

        if ($lastCheckin) {
            $daysSince = Carbon::parse($lastCheckin->checkin_date)->diffInDays(Carbon::today());

            return "Bạn đã không check-in trong {$daysSince} ngày. Hãy cập nhật tình trạng sức khỏe để chúng tôi có thể theo dõi tốt hơn.";
        }

        return 'Bạn chưa có check-in nào. Hãy bắt đầu theo dõi sức khỏe hàng ngày.';
    }

    private function generateSymptomCombinationMessage(User $user, array $condition): string
    {
        $symptoms = $condition['symptoms'] ?? [];
        $symptomNames = [];

        foreach ($symptoms as $symptomConfig) {
            $code = $symptomConfig['code'] ?? null;
            if ($code) {
                $symptomNames[] = $this->getSymptomDisplayName($code);
            }
        }

        if (empty($symptomNames)) {
            return 'Bạn đang có nhiều triệu chứng cùng lúc. Hãy theo dõi sát và cân nhắc đi khám nếu cần thiết.';
        }

        $symptomsList = implode(', ', $symptomNames);

        return "Bạn đang có các triệu chứng: {$symptomsList}. Các triệu chứng này xuất hiện cùng lúc có thể cần được theo dõi kỹ hơn.";
    }

    private function generateNewSymptomMessage(User $user, array $condition): string
    {
        $symptomCode = $condition['symptom'] ?? null;
        $symptomName = $this->getSymptomDisplayName($symptomCode);

        return "Bạn có triệu chứng mới: {$symptomName}. Hãy theo dõi và ghi nhận mức độ nghiêm trọng của triệu chứng này.";
    }

    private function generateMedicationNonComplianceMessage(User $user, array $condition): string
    {
        $medicationName = $condition['medication'] ?? 'thuốc';
        $missedDays = $condition['missed_days'] ?? 2;

        return "Bạn đã bỏ lỡ uống {$medicationName} trong {$missedDays} ngày. Hãy nhớ uống thuốc đúng liều và đúng giờ để đạt hiệu quả điều trị tốt nhất.";
    }

    private function generateMedicationSideEffectMessage(User $user, array $condition): string
    {
        $medicationName = $condition['medication'] ?? 'thuốc';
        $sideEffect = $condition['side_effect'] ?? 'tác dụng phụ';

        return "Bạn có thể đang gặp tác dụng phụ từ {$medicationName}: {$sideEffect}. Hãy liên hệ bác sĩ nếu tình trạng này kéo dài hoặc trở nên nghiêm trọng.";
    }

    private function generateTreatmentNoImprovementMessage(User $user, array $condition): string
    {
        $symptomCode = $condition['symptom'] ?? null;
        $daysSinceStart = $condition['days_since_start'] ?? 5;
        $symptomName = $this->getSymptomDisplayName($symptomCode);

        return "Triệu chứng {$symptomName} của bạn vẫn chưa cải thiện sau {$daysSinceStart} ngày. Bạn nên cân nhắc điều chỉnh phương pháp điều trị hoặc liên hệ bác sĩ để được tư vấn.";
    }

    private function generatePostVaccineAbnormalMessage(User $user, array $condition): string
    {
        $hoursSinceVaccine = $condition['hours_since_vaccine'] ?? 72;
        $days = round($hoursSinceVaccine / 24, 1);

        return "Bạn có triệu chứng sốt sau tiêm vaccine đã hơn {$days} ngày. Đây có thể là phản ứng bất thường. Bạn nên liên hệ bác sĩ hoặc đến cơ sở y tế để được kiểm tra.";
    }

    /**
     * Get symptom display name from code.
     */
    private function getSymptomDisplayName(?string $symptomCode): string
    {
        if (! $symptomCode) {
            return 'triệu chứng';
        }

        $symptom = \App\Models\Symptom::where('code', $symptomCode)->first();

        return $symptom ? $symptom->display_name : $symptomCode;
    }
}
