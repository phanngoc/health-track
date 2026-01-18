<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyCheckin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'checkin_date',
        'mood',
        'tags',
        'overall_feeling',
        'sleep_hours',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'checkin_date' => 'date',
            'tags' => 'array',
            'overall_feeling' => 'integer',
            'sleep_hours' => 'float',
        ];
    }

    /**
     * Get the user that owns the check-in.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the moment check-ins for this date.
     */
    public function momentCheckins(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MomentCheckin::class, 'user_id', 'user_id')
            ->whereDate('occurred_at', $this->checkin_date);
    }
}
