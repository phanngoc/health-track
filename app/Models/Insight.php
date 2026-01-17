<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Insight extends Model
{
    /** @use HasFactory<\Database\Factories\InsightFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'code',
        'message',
        'priority',
        'metadata',
        'explanation_data',
        'generated_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'explanation_data' => 'array',
            'generated_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the insight.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
