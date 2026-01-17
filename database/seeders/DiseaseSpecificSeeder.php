<?php

namespace Database\Seeders;

use App\Models\AlertRule;
use App\Models\Symptom;
use Illuminate\Database\Seeder;

class DiseaseSpecificSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds symptoms and alert rules for 3 specific diseases:
     * 1. Viêm mũi dị ứng (Allergic Rhinitis)
     * 2. Viêm da cơ địa (Atopic Dermatitis)
     * 3. Phụ nữ mang thai (Pregnancy-safe Monitoring)
     */
    public function run(): void
    {
        $this->seedSymptoms();
        $this->seedAlertRules();
    }

    /**
     * Seed symptoms for all 3 diseases.
     */
    private function seedSymptoms(): void
    {
        $symptoms = [
            // I. Viêm mũi dị ứng (Allergic Rhinitis)
            ['code' => 'sneezing', 'display_name' => 'Hắt hơi', 'severity_scale' => 100, 'is_critical' => false], // lần/ngày
            ['code' => 'runny_nose', 'display_name' => 'Chảy mũi', 'severity_scale' => 10, 'is_critical' => false],
            ['code' => 'itchy_nose', 'display_name' => 'Ngứa mũi', 'severity_scale' => 10, 'is_critical' => false],

            // II. Viêm da cơ địa (Atopic Dermatitis)
            ['code' => 'itch', 'display_name' => 'Ngứa da', 'severity_scale' => 10, 'is_critical' => false],
            ['code' => 'redness', 'display_name' => 'Đỏ da', 'severity_scale' => 10, 'is_critical' => false],
            ['code' => 'oozing', 'display_name' => 'Rỉ dịch da', 'severity_scale' => 2, 'is_critical' => true], // boolean (0-1)
            ['code' => 'cracked_skin', 'display_name' => 'Nứt da', 'severity_scale' => 2, 'is_critical' => true], // boolean (0-1)
            ['code' => 'sleep_disturbance', 'display_name' => 'Ảnh hưởng giấc ngủ', 'severity_scale' => 10, 'is_critical' => false],

            // III. Phụ nữ mang thai (Pregnancy-safe Monitoring)
            ['code' => 'vaginal_bleeding', 'display_name' => 'Ra máu', 'severity_scale' => 2, 'is_critical' => true], // boolean (0-1)
            ['code' => 'severe_headache', 'display_name' => 'Đau đầu dữ dội', 'severity_scale' => 10, 'is_critical' => true],
            ['code' => 'blurred_vision', 'display_name' => 'Mờ mắt', 'severity_scale' => 2, 'is_critical' => true], // boolean (0-1)
            ['code' => 'swelling', 'display_name' => 'Phù tay/chân/mặt', 'severity_scale' => 10, 'is_critical' => true],
            ['code' => 'fetal_movement', 'display_name' => 'Thai máy', 'severity_scale' => 3, 'is_critical' => true], // decreased=0, normal=1, increased=2
        ];

        foreach ($symptoms as $symptom) {
            Symptom::updateOrCreate(
                ['code' => $symptom['code']],
                $symptom
            );
        }
    }

    /**
     * Seed alert rules for all 3 diseases.
     */
    private function seedAlertRules(): void
    {
        $rules = [
            // ============================================
            // I. VIÊM MŨI DỊ ỨNG (Allergic Rhinitis)
            // ============================================

            // AR-01: Triệu chứng kéo dài (chuyển mạn)
            [
                'code' => 'AR_01_PERSISTENT_CONGESTION',
                'severity' => 'warning',
                'condition' => [
                    'symptom' => 'nasal_congestion',
                    'min_severity' => 5,
                    'duration_days' => 5,
                ],
                'cooldown_hours' => 24,
                'is_active' => true,
            ],

            // AR-02: Pattern điển hình dị ứng
            [
                'code' => 'AR_02_CLASSIC_ALLERGY_PATTERN',
                'severity' => 'watch',
                'condition' => [
                    'symptoms' => [
                        ['code' => 'sneezing', 'min_severity' => 10],
                        ['code' => 'itchy_nose', 'min_severity' => 5],
                        ['code' => 'runny_nose', 'min_severity' => 5],
                    ],
                ],
                'cooldown_hours' => 24,
                'is_active' => true,
            ],

            // AR-03: Nghi ngờ biến chứng xoang
            [
                'code' => 'AR_03_SINUS_COMPLICATION',
                'severity' => 'warning',
                'condition' => [
                    'symptoms' => [
                        ['code' => 'nasal_congestion', 'min_severity' => 6],
                        ['code' => 'headache', 'min_severity' => 6],
                    ],
                    'duration_days' => 3,
                ],
                'cooldown_hours' => 24,
                'is_active' => true,
            ],

            // ============================================
            // II. VIÊM DA CƠ ĐỊA (Atopic Dermatitis)
            // ============================================

            // AD-01: Đợt bùng phát cấp
            [
                'code' => 'AD_01_ACUTE_FLARE',
                'severity' => 'warning',
                'condition' => [
                    'symptoms' => [
                        ['code' => 'itch', 'min_severity' => 7],
                        ['code' => 'redness', 'min_severity' => 6],
                    ],
                ],
                'cooldown_hours' => 24,
                'is_active' => true,
            ],

            // AD-02: Ngứa ảnh hưởng giấc ngủ
            [
                'code' => 'AD_02_SLEEP_IMPACT',
                'severity' => 'warning',
                'condition' => [
                    'symptoms' => [
                        ['code' => 'itch', 'min_severity' => 6],
                        ['code' => 'sleep_disturbance', 'min_severity' => 5],
                    ],
                ],
                'cooldown_hours' => 24,
                'is_active' => true,
            ],

            // AD-03: Nghi nhiễm trùng da
            [
                'code' => 'AD_03_INFECTION_RISK',
                'severity' => 'critical',
                'condition' => [
                    'symptoms' => [
                        ['code' => 'oozing', 'min_severity' => 1],
                        ['code' => 'cracked_skin', 'min_severity' => 1],
                    ],
                    'any_match' => true, // OR condition
                ],
                'cooldown_hours' => 12,
                'is_active' => true,
            ],

            // AD-04: Không cải thiện theo thời gian
            [
                'code' => 'AD_04_NO_IMPROVEMENT',
                'severity' => 'watch',
                'condition' => [
                    'symptom' => 'itch',
                    'min_severity' => 5,
                    'duration_days' => 7,
                ],
                'cooldown_hours' => 48,
                'is_active' => true,
            ],

            // ============================================
            // III. PHỤ NỮ MANG THAI (Pregnancy-safe Monitoring)
            // ============================================

            // PR-01: Ra máu khi mang thai
            [
                'code' => 'PR_01_BLEEDING',
                'severity' => 'critical',
                'condition' => [
                    'symptom' => 'vaginal_bleeding',
                    'min_severity' => 1,
                ],
                'cooldown_hours' => 0, // No cooldown for critical
                'is_active' => true,
            ],

            // PR-02: Nghi tiền sản giật
            [
                'code' => 'PR_02_PREECLAMPSIA',
                'severity' => 'critical',
                'condition' => [
                    'symptoms' => [
                        ['code' => 'severe_headache', 'min_severity' => 1],
                        ['code' => 'blurred_vision', 'min_severity' => 1],
                        ['code' => 'swelling', 'min_severity' => 1],
                    ],
                ],
                'cooldown_hours' => 0, // No cooldown for critical
                'is_active' => true,
            ],

            // PR-03: Đau bụng kéo dài
            [
                'code' => 'PR_03_PERSISTENT_PAIN',
                'severity' => 'warning',
                'condition' => [
                    'symptom' => 'stomach_pain',
                    'min_severity' => 5,
                    'duration_hours' => 6,
                ],
                'cooldown_hours' => 12,
                'is_active' => true,
            ],

            // PR-04: Thai máy giảm
            [
                'code' => 'PR_04_REDUCED_FETAL_MOVEMENT',
                'severity' => 'critical',
                'condition' => [
                    'symptom' => 'fetal_movement',
                    'min_severity' => 0,
                    'max_severity' => 0, // decreased = 0
                ],
                'cooldown_hours' => 0, // No cooldown for critical
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
