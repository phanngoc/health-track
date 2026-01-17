<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'medication_id',
        'taken_at',
        'missed',
    ];

    protected function casts(): array
    {
        return [
            'taken_at' => 'datetime',
            'missed' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the medication log.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the medication.
     */
    public function medication(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }
}
