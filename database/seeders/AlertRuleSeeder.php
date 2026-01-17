<?php

namespace Database\Seeders;

use App\Models\AlertRule;
use Illuminate\Database\Seeder;

class AlertRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rules = [
            // A1: Symptom duration exceeded - Nasal congestion
            [
                'code' => 'symptom_duration_exceeded_nasal_congestion',
                'severity' => 'warning',
                'condition' => [
                    'symptom' => 'nasal_congestion',
                    'min_severity' => 4,
                    'duration_days' => 5,
                ],
                'cooldown_hours' => 24,
                'is_active' => true,
            ],
            // A1: Symptom duration exceeded - Headache
            [
                'code' => 'symptom_duration_exceeded_headache',
                'severity' => 'warning',
                'condition' => [
                    'symptom' => 'headache',
                    'min_severity' => 4,
                    'duration_days' => 3,
                ],
                'cooldown_hours' => 24,
                'is_active' => true,
            ],
            // A1: Symptom duration exceeded - Cough
            [
                'code' => 'symptom_duration_exceeded_cough',
                'severity' => 'warning',
                'condition' => [
                    'symptom' => 'cough',
                    'min_severity' => 4,
                    'duration_days' => 7,
                ],
                'cooldown_hours' => 24,
                'is_active' => true,
            ],

            // A2: Severity increasing
            [
                'code' => 'severity_increasing_headache',
                'severity' => 'warning',
                'condition' => [
                    'symptom' => 'headache',
                    'increase_threshold' => 3,
                    'days_back' => 3,
                ],
                'cooldown_hours' => 24,
                'is_active' => true,
            ],

            // A3: Missing check-in with symptom
            [
                'code' => 'missing_checkin_with_symptom',
                'severity' => 'watch',
                'condition' => [
                    'missing_days' => 2,
                    'min_severity' => 5,
                ],
                'cooldown_hours' => 48,
                'is_active' => true,
            ],

            // B1: Symptom combination warning
            [
                'code' => 'symptom_combination_warning_headache_fever',
                'severity' => 'warning',
                'condition' => [
                    'symptoms' => [
                        ['code' => 'headache', 'min_severity' => 5],
                        ['code' => 'fever', 'min_severity' => 38],
                    ],
                ],
                'cooldown_hours' => 24,
                'is_active' => true,
            ],

            // B2: Symptom combination critical
            [
                'code' => 'symptom_combination_critical_chest_breath',
                'severity' => 'critical',
                'condition' => [
                    'symptoms' => [
                        ['code' => 'chest_pain', 'min_severity' => 5],
                        ['code' => 'shortness_of_breath', 'min_severity' => 1],
                    ],
                ],
                'cooldown_hours' => 12,
                'is_active' => true,
            ],

            // B3: New symptom with condition
            [
                'code' => 'new_symptom_with_condition_dizziness_hypertension',
                'severity' => 'warning',
                'condition' => [
                    'symptom' => 'dizziness',
                    'conditions' => ['hypertension'],
                ],
                'cooldown_hours' => 24,
                'is_active' => true,
            ],

            // C1: Medication non-compliance
            [
                'code' => 'medication_non_compliance',
                'severity' => 'watch',
                'condition' => [
                    'missed_days' => 2,
                ],
                'cooldown_hours' => 48,
                'is_active' => true,
            ],

            // C2: Medication side effect
            [
                'code' => 'medication_side_effect_antibiotic_rash',
                'severity' => 'warning',
                'condition' => [
                    'medication_type' => 'antibiotic',
                    'side_effect_symptom' => 'rash',
                ],
                'cooldown_hours' => 24,
                'is_active' => true,
            ],

            // C3: Treatment no improvement
            [
                'code' => 'treatment_no_improvement_headache',
                'severity' => 'warning',
                'condition' => [
                    'symptom' => 'headache',
                    'days_since_start' => 5,
                ],
                'cooldown_hours' => 24,
                'is_active' => true,
            ],

            // D2: Post-vaccine abnormal
            [
                'code' => 'post_vaccine_abnormal',
                'severity' => 'warning',
                'condition' => [
                    'hours_since_vaccine' => 72,
                    'min_fever' => 38,
                ],
                'cooldown_hours' => 24,
                'is_active' => true,
            ],
        ];

        foreach ($rules as $rule) {
            AlertRule::updateOrCreate(
                ['code' => $rule['code']],
                $rule
            );
        }
    }
}
