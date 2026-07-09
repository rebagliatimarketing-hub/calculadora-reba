<?php

namespace App\Modules\AcademicEvents\Services;

use App\Models\AcademicEvent;
use App\Models\EventSession;
use App\Models\Holiday;
use App\Shared\DTOs\ScheduleGenerationOptions;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class EventScheduleGenerator
{
    public function generate(AcademicEvent $event, ScheduleGenerationOptions $options): Collection
    {
        $event->loadMissing(['structure', 'modality']);

        $structure = $event->structure;
        $classesCount = $structure?->classes_count ?: 1;
        $sessions = collect();
        $currentDate = $this->nextAllowedDate($options->startDate, $options->allowedWeekdays);

        for ($sessionNumber = 1; $sessionNumber <= $classesCount; $sessionNumber++) {
            $start = CarbonImmutable::parse($currentDate->format('Y-m-d').' '.$options->startTime);
            $end = $start->addMinutes($options->classDurationMinutes);

            $sessions->push([
                'academic_event_id' => $event->id,
                'session_number' => $sessionNumber,
                'module_number' => $sessionNumber,
                'session_type' => 'CLASE',
                'title' => 'Modulo '.$sessionNumber,
                'date' => $currentDate->toDateString(),
                'start_time' => $start->format('H:i:s'),
                'end_time' => $end->format('H:i:s'),
                'duration_minutes' => $options->classDurationMinutes,
                'modality_id' => $event->modality_id,
                'room_id' => $event->modality->requires_room ? $options->roomId : null,
                'zoom_account_id' => $event->modality->requires_zoom ? $options->zoomAccountId : null,
                'speaker_id' => $options->speakerId,
                'status' => 'TENTATIVA',
                'is_holiday' => Holiday::query()->whereDate('date', $currentDate->toDateString())->exists(),
                'is_exception' => false,
            ]);

            $currentDate = $this->nextDateByFrequency($currentDate, $options);
        }

        if ($structure?->has_workshops) {
            $workshopDate = $this->nextAllowedDate($currentDate, $options->allowedWeekdays);
            $start = CarbonImmutable::parse($workshopDate->format('Y-m-d').' 15:00');
            $sessions->push([
                'academic_event_id' => $event->id,
                'session_number' => $classesCount + 1,
                'module_number' => $classesCount,
                'session_type' => $structure->has_presential_workshops ? 'TALLER_PRESENCIAL' : 'TALLER_VIRTUAL',
                'title' => 'Taller aplicado',
                'date' => $workshopDate->toDateString(),
                'start_time' => $start->format('H:i:s'),
                'end_time' => $start->addMinutes(180)->format('H:i:s'),
                'duration_minutes' => 180,
                'modality_id' => $event->modality_id,
                'room_id' => $structure->has_presential_workshops ? $options->roomId : null,
                'zoom_account_id' => $structure->has_virtual_workshops ? $options->zoomAccountId : null,
                'speaker_id' => $options->speakerId,
                'status' => 'TENTATIVA',
                'is_holiday' => Holiday::query()->whereDate('date', $workshopDate->toDateString())->exists(),
                'is_exception' => false,
            ]);
        }

        return $sessions;
    }

    public function persist(AcademicEvent $event, ScheduleGenerationOptions $options): Collection
    {
        EventSession::query()->where('academic_event_id', $event->id)->delete();

        return $this->generate($event, $options)->map(fn (array $session) => EventSession::query()->create($session));
    }

    private function nextAllowedDate(CarbonImmutable $date, array $allowedWeekdays): CarbonImmutable
    {
        $allowedWeekdays = $allowedWeekdays ?: [6];

        while (! in_array((int) $date->isoWeekday(), $allowedWeekdays, true)) {
            $date = $date->addDay();
        }

        return $date;
    }

    private function nextDateByFrequency(CarbonImmutable $date, ScheduleGenerationOptions $options): CarbonImmutable
    {
        $nextDate = match ($options->frequencyType) {
            'weekly' => $date->addWeek(),
            'biweekly' => $date->addWeeks(2),
            default => $date->addMonthNoOverflow(),
        };

        return $this->nextAllowedDate($nextDate, $options->allowedWeekdays);
    }
}
