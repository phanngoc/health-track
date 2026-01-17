<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medication extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'known_side_effects',
    ];

    protected function casts(): array
    {
        return [
            'known_side_effects' => 'array',
        ];
    }

    /**
     * Get the medication logs for this medication.
     */
    public function medicationLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MedicationLog::class);
    }
}
