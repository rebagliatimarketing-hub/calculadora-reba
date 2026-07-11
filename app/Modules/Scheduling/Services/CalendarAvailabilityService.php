<?php

namespace App\Modules\Scheduling\Services;

use App\Models\EventSession;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class CalendarAvailabilityService
{
    public function findAvailableSlots(array $criteria): Collection
    {
        $start = CarbonImmutable::parse($criteria['start_date']);
        $end = CarbonImmutable::parse($criteria['end_date']);
        $duration = (int) ($criteria['duration_minutes'] ?? 180);
        $slots = collect();

        for ($date = $start; $date->lte($end); $date = $date->addDay()) {
            if (! in_array((int) $date->isoWeekday(), $criteria['allowed_weekdays'] ?? [6], true)) {
                continue;
            }

            foreach (['09:00', '15:00', '19:00'] as $time) {
                $candidateStart = CarbonImmutable::parse($date->toDateString().' '.$time);
                $candidateEnd = $candidateStart->addMinutes($duration);
                $overlapQuery = EventSession::query()
                    ->whereDate('date', $date)
                    ->where('start_time', '<', $candidateEnd->format('H:i:s'))
                    ->where('end_time', '>', $candidateStart->format('H:i:s'));

                if ((bool) ($criteria['requires_room'] ?? false)) {
                    $overlapQuery->whereHas('modality', fn ($query) => $query->where('requires_room', true));
                } elseif (! empty($criteria['speaker_id'])) {
                    $overlapQuery->where('speaker_id', $criteria['speaker_id']);
                } else {
                    $overlapQuery->whereRaw('1 = 0');
                }

                $hasOverlap = $overlapQuery->exists();

                if (! $hasOverlap) {
                    $slots->push([
                        'date' => $date->toDateString(),
                        'start_time' => $candidateStart->format('H:i'),
                        'end_time' => $candidateEnd->format('H:i'),
                    ]);
                }
            }
        }

        return $slots->take(10);
    }
}
