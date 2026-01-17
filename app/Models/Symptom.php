<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Symptom extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'display_name',
        'severity_scale',
        'is_critical',
    ];

    protected function casts(): array
    {
        return [
            'is_critical' => 'boolean',
        ];
    }

    /**
     * Get the symptom logs for this symptom.
     */
    public function symptomLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SymptomLog::class, 'symptom_code', 'code');
    }
}
