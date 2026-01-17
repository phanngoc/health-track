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
        'overall_feeling',
        'sleep_hours',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'checkin_date' => 'date',
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
}
