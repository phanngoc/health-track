<?php

namespace Database\Seeders;

use App\Models\Symptom;
use Illuminate\Database\Seeder;

class SymptomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $symptoms = [
            ['code' => 'headache', 'display_name' => 'Đau đầu', 'severity_scale' => 10, 'is_critical' => false],
            ['code' => 'fever', 'display_name' => 'Sốt', 'severity_scale' => 10, 'is_critical' => false],
            ['code' => 'cough', 'display_name' => 'Ho', 'severity_scale' => 10, 'is_critical' => false],
            ['code' => 'nasal_congestion', 'display_name' => 'Nghẹt mũi', 'severity_scale' => 10, 'is_critical' => false],
            ['code' => 'chest_pain', 'display_name' => 'Đau ngực', 'severity_scale' => 10, 'is_critical' => true],
            ['code' => 'shortness_of_breath', 'display_name' => 'Khó thở', 'severity_scale' => 10, 'is_critical' => true],
            ['code' => 'dizziness', 'display_name' => 'Chóng mặt', 'severity_scale' => 10, 'is_critical' => false],
            ['code' => 'nausea', 'display_name' => 'Buồn nôn', 'severity_scale' => 10, 'is_critical' => false],
            ['code' => 'rash', 'display_name' => 'Phát ban', 'severity_scale' => 10, 'is_critical' => false],
            ['code' => 'fatigue', 'display_name' => 'Mệt mỏi', 'severity_scale' => 10, 'is_critical' => false],
            ['code' => 'stomach_pain', 'display_name' => 'Đau bụng', 'severity_scale' => 10, 'is_critical' => false],
            ['code' => 'sore_throat', 'display_name' => 'Đau họng', 'severity_scale' => 10, 'is_critical' => false],
        ];

        foreach ($symptoms as $symptom) {
            Symptom::updateOrCreate(
                ['code' => $symptom['code']],
                $symptom
            );
        }
    }
}
