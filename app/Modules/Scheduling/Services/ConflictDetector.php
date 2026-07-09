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
        $event->loadMissing(['sessions', 'modality', 'audience', 'specialty']);
        SchedulingConflict::query()->where('academic_event_id', $event->id)->where('status', 'ABIERTO')->delete();

        return $event->sessions->flatMap(fn (EventSession $session) => $this->detectForSession($event, $session));
    }

    private function detectForSession(AcademicEvent $event, EventSession $session): Collection
    {
        $conflicts = collect();

        if ($session->is_holiday && $event->modality->requires_room) {
            $conflicts->push($this->createConflict($event, $session, 'HOLIDAY_PRESENTIAL', 'BLOQUEANTE', 'La sesion presencial cae en feriado.', 'Mover la sesion al siguiente fin de semana disponible.'));
        }

        if (! in_array((int) $session->date->isoWeekday(), [5, 6, 7], true)) {
            $conflicts->push($this->createConflict($event, $session, 'WEAK_WEEKDAY', 'ADVERTENCIA', 'La sesion esta programada fuera de viernes, sabado o domingo.', 'Validar si el publico objetivo puede asistir en este dia.'));
        }

        $overlaps = EventSession::query()
            ->with('academicEvent')
            ->whereDate('date', $session->date)
            ->where('id', '!=', $session->id)
            ->where(function ($query) use ($session): void {
                $query->whereBetween('start_time', [$session->start_time, $session->end_time])
                    ->orWhereBetween('end_time', [$session->start_time, $session->end_time])
                    ->orWhere(function ($query) use ($session): void {
                        $query->where('start_time', '<=', $session->start_time)
                            ->where('end_time', '>=', $session->end_time);
                    });
            })
            ->get();

        foreach ($overlaps as $overlap) {
            if ($session->room_id && $overlap->room_id === $session->room_id) {
                $conflicts->push($this->createConflict($event, $session, 'ROOM_OVERLAP', 'CRITICO', 'El aula ya esta ocupada por '.$overlap->academicEvent->name.'.', 'Cambiar de aula o mover la sesion a otro turno.', $overlap));
            }

            if ($session->zoom_account_id && $overlap->zoom_account_id === $session->zoom_account_id) {
                $conflicts->push($this->createConflict($event, $session, 'ZOOM_OVERLAP', 'CRITICO', 'La cuenta Zoom ya esta ocupada por '.$overlap->academicEvent->name.'.', 'Asignar otra cuenta Zoom o mover la sesion.', $overlap));
            }

            if ($session->speaker_id && $overlap->speaker_id === $session->speaker_id) {
                $conflicts->push($this->createConflict($event, $session, 'SPEAKER_OVERLAP', 'ALTO', 'El docente tambien participa en '.$overlap->academicEvent->name.'.', 'Confirmar disponibilidad del docente o cambiar horario.', $overlap));
            }

            if ($overlap->academicEvent->audience_segment_id === $event->audience_segment_id) {
                $conflicts->push($this->createConflict($event, $session, 'AUDIENCE_OVERLAP', 'ADVERTENCIA', 'Dos eventos compiten por el mismo publico objetivo.', 'Separar las convocatorias o justificar la simultaneidad.', $overlap));
            }
        }

        return $conflicts;
    }

    private function createConflict(AcademicEvent $event, EventSession $session, string $ruleCode, string $severity, string $message, string $recommendation, ?EventSession $overlap = null): SchedulingConflict
    {
        $rule = SchedulingRule::query()->firstWhere('code', $ruleCode);

        return SchedulingConflict::query()->create([
            'academic_event_id' => $event->id,
            'event_session_id' => $session->id,
            'conflict_event_id' => $overlap?->academic_event_id,
            'conflict_session_id' => $overlap?->id,
            'rule_id' => $rule?->id,
            'severity' => $severity,
            'message' => $message,
            'recommendation' => $recommendation,
            'status' => 'ABIERTO',
        ]);
    }
}
