<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SymptomLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'symptom_code',
        'severity',
        'occurred_at',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'severity' => 'integer',
            'occurred_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the symptom log.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the symptom definition.
     */
    public function symptom(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Symptom::class, 'symptom_code', 'code');
    }
}
