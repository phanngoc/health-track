<?php

namespace App\Services;

use App\Models\TimelineEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class TimelineService
{
    /**
     * Add an event to the user's timeline.
     */
    public function addEvent(User $user, string $eventType, ?int $refId = null, ?Carbon $occurredAt = null): TimelineEvent
    {
        return TimelineEvent::create([
            'user_id' => $user->id,
            'event_type' => $eventType,
            'ref_id' => $refId,
            'occurred_at' => $occurredAt ?? Carbon::now(),
        ]);
    }

    /**
     * Get the user's timeline events.
     *
     * @return Collection<int, TimelineEvent>
     */
    public function getTimeline(User $user, ?Carbon $startDate = null, ?Carbon $endDate = null): Collection
    {
        $query = TimelineEvent::where('user_id', $user->id)
            ->orderBy('occurred_at', 'desc');

        if ($startDate) {
            $query->where('occurred_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('occurred_at', '<=', $endDate);
        }

        return $query->get();
    }

    /**
     * Get timeline events grouped by date.
     *
     * @return array<string, Collection<int, TimelineEvent>>
     */
    public function getTimelineGroupedByDate(User $user, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $events = $this->getTimeline($user, $startDate, $endDate);

        return $events->groupBy(function ($event) {
            return $event->occurred_at->format('Y-m-d');
        })->toArray();
    }
}
