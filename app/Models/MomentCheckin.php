<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MomentCheckin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'feeling_level',
        'mood',
        'tags',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'occurred_at' => 'datetime',
            'feeling_level' => 'integer',
        ];
    }

    /**
     * Get the user that owns the moment check-in.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
