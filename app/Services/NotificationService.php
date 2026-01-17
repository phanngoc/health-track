<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\User;
use Illuminate\Notifications\Notification;

class NotificationService
{
    /**
     * Send an alert notification to the user.
     */
    public function sendAlert(User $user, Alert $alert): void
    {
        // For MVP, we'll use Laravel's built-in notification system
        // In production, this would integrate with FCM/APNs for push notifications

        $user->notify(new class($alert) extends Notification
        {
            public function __construct(
                private Alert $alert
            ) {
            }

            public function via($notifiable): array
            {
                return ['database'];
            }

            public function toArray($notifiable): array
            {
                return [
                    'alert_id' => $this->alert->id,
                    'severity' => $this->alert->severity,
                    'message' => $this->alert->message,
                    'triggered_at' => $this->alert->triggered_at->toIso8601String(),
                ];
            }
        });
    }
}
