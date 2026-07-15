<?php

namespace App\Modules\Scheduling\Services;

use App\Models\AcademicEvent;
use App\Models\EventSession;
use App\Models\SchedulingConflict;
use App\Models\SchedulingRule;
use Illuminate\Support\Collection;

class ConflictDetector
{
    public function detectForEvent(AcademicEvent $event): Collection
    {
        $event->load(['sessions', 'modality']);
        SchedulingConflict::query()->where('academic_event_id', $event->id)->where('status', 'ABIERTO')->delete();

        if ($event->sessions->isEmpty()) {
            return collect();
        }

        $rules = SchedulingRule::query()
            ->whereIn('code', ['HOLIDAY_PRESENTIAL', 'WEAK_WEEKDAY', 'ROOM_OVERLAP', 'SPEAKER_OVERLAP'])
            ->get()
            ->keyBy('code');

        $sessionDates = $event->sessions->pluck('date')->map->toDateString()->sort()->values();

        $overlapsByDate = EventSession::query()
            ->join('academic_events', 'academic_events.id', '=', 'event_sessions.academic_event_id')
            ->join('modalities', 'modalities.id', '=', 'academic_events.modality_id')
            ->whereDate('event_sessions.date', '>=', $sessionDates->first())
            ->whereDate('event_sessions.date', '<=', $sessionDates->last())
            ->where('event_sessions.academic_event_id', '!=', $event->id)
            ->whereNull('academic_events.deleted_at')
            ->select([
                'event_sessions.*',
                'academic_events.name as event_name',
                'modalities.requires_room as event_requires_room',
            ])
            ->get();

        $overlapsByDate = $overlapsByDate->groupBy(fn (EventSession $session) => $session->date->toDateString());
        $rows = collect();

        foreach ($event->sessions as $session) {
            if ($session->is_holiday && $event->modality->requires_room) {
                $rows->push($this->conflictData(
                    $event,
                    $session,
                    $rules->get('HOLIDAY_PRESENTIAL'),
                    'BLOQUEANTE',
                    'La sesion presencial cae en feriado.',
                    'Mover la sesion al siguiente fin de semana disponible.',
                ));
            }

            if (! in_array((int) $session->date->isoWeekday(), [5, 6, 7], true)) {
                $rows->push($this->conflictData(
                    $event,
                    $session,
                    $rules->get('WEAK_WEEKDAY'),
                    'ADVERTENCIA',
                    'La sesion esta programada fuera de viernes, sabado o domingo.',
                    'Validar si el publico objetivo puede asistir en este dia.',
                ));
            }

            $overlaps = $overlapsByDate
                ->get($session->date->toDateString(), collect())
                ->filter(fn (EventSession $overlap) => $overlap->start_time < $session->end_time
                    && $overlap->end_time > $session->start_time);

            foreach ($overlaps as $overlap) {
                $bothRequireRoom = $event->modality->requires_room && $overlap->event_requires_room;

                if ($bothRequireRoom) {
                    $rows->push($this->conflictData(
                        $event,
                        $session,
                        $rules->get('ROOM_OVERLAP'),
                        'CRITICO',
                        'Existe un cruce presencial con '.$overlap->event_name.'.',
                        'Mover una de las sesiones a otra fecha u horario antes de aprobar.',
                        $overlap,
                    ));
                }

                if ($session->speaker_id && $overlap->speaker_id === $session->speaker_id) {
                    $rows->push($this->conflictData(
                        $event,
                        $session,
                        $rules->get('SPEAKER_OVERLAP'),
                        'ALTO',
                        'El docente tambien participa en '.$overlap->event_name.'.',
                        'Confirmar disponibilidad del docente o cambiar horario.',
                        $overlap,
                    ));
                }
            }
        }

        if ($rows->isNotEmpty()) {
            SchedulingConflict::query()->insert($rows->all());
        }

        return SchedulingConflict::query()
            ->with('rule')
            ->where('academic_event_id', $event->id)
            ->where('status', 'ABIERTO')
            ->get();
    }

    private function conflictData(
        AcademicEvent $event,
        EventSession $session,
        ?SchedulingRule $rule,
        string $severity,
        string $message,
        string $recommendation,
        ?EventSession $overlap = null,
    ): array {
        $now = now();

        return [
            'academic_event_id' => $event->id,
            'event_session_id' => $session->id,
            'conflict_event_id' => $overlap?->academic_event_id,
            'conflict_session_id' => $overlap?->id,
            'rule_id' => $rule?->id,
            'severity' => $severity,
            'message' => $message,
            'recommendation' => $recommendation,
            'status' => 'ABIERTO',
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}
