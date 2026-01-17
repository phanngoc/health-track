<?php

namespace Database\Seeders;

use App\Models\Medication;
use Illuminate\Database\Seeder;

class MedicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medications = [
            [
                'name' => 'Amoxicillin',
                'type' => 'antibiotic',
                'known_side_effects' => ['rash', 'nausea', 'diarrhea'],
            ],
            [
                'name' => 'Ibuprofen',
                'type' => 'pain_reliever',
                'known_side_effects' => ['stomach_pain', 'dizziness'],
            ],
            [
                'name' => 'COVID-19 Vaccine',
                'type' => 'vaccine',
                'known_side_effects' => ['fever', 'fatigue', 'headache'],
            ],
            [
                'name' => 'Paracetamol',
                'type' => 'pain_reliever',
                'known_side_effects' => [],
            ],
        ];

        foreach ($medications as $medication) {
            Medication::updateOrCreate(
                ['name' => $medication['name']],
                $medication
            );
        }
    }
}
