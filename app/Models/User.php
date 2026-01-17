<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'age',
        'gender',
        'conditions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'conditions' => 'array',
        ];
    }

    /**
     * Get the daily check-ins for the user.
     */
    public function dailyCheckins(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DailyCheckin::class);
    }

    /**
     * Get the symptom logs for the user.
     */
    public function symptomLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SymptomLog::class);
    }

    /**
     * Get the medication logs for the user.
     */
    public function medicationLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MedicationLog::class);
    }

    /**
     * Get the health events for the user.
     */
    public function healthEvents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(HealthEvent::class);
    }

    /**
     * Get the alerts for the user.
     */
    public function alerts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Alert::class);
    }

    /**
     * Get the timeline events for the user.
     */
    public function timelineEvents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TimelineEvent::class);
    }

    /**
     * Get the insights for the user.
     */
    public function insights(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Insight::class);
    }

    /**
     * Check if user has pregnancy condition.
     */
    public function isPregnant(): bool
    {
        $conditions = $this->conditions ?? [];

        return in_array('pregnancy', $conditions) || in_array('pregnant', $conditions);
    }

    /**
     * Get user's disease type (AR, AD, or null).
     */
    public function getDiseaseType(): ?string
    {
        $conditions = $this->conditions ?? [];

        if (in_array('allergic_rhinitis', $conditions) || in_array('AR', $conditions)) {
            return 'AR';
        }

        if (in_array('atopic_dermatitis', $conditions) || in_array('AD', $conditions)) {
            return 'AD';
        }

        if ($this->isPregnant()) {
            return 'PR';
        }

        return null;
    }
}
