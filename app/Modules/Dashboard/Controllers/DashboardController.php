<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicEvent;
use App\Models\EventSession;
use App\Models\SchedulingConflict;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $month = now()->month;
        $year = now()->year;

        $metrics = (array) DB::query()->selectRaw('(
                select count(*)
                from launch_proposals
                inner join launch_cycles on launch_cycles.id = launch_proposals.launch_cycle_id
                where launch_cycles.month = ? and launch_cycles.year = ?
            ) as total,
            (select count(*) from launch_proposals where status = ?) as approved,
            (select count(*) from launch_proposals where status in (?, ?, ?)) as review,
            (select count(*) from scheduling_conflicts where status = ?) as conflicts,
            (
                select count(*)
                from academic_events
                inner join modalities on modalities.id = academic_events.modality_id
                where modalities.requires_room = ? and academic_events.deleted_at is null
            ) as presential,
            (
                select count(*)
                from academic_events
                inner join modalities on modalities.id = academic_events.modality_id
                where modalities.requires_zoom = ? and academic_events.deleted_at is null
            ) as virtual', [
            $month,
            $year,
            'APROBADO_FINAL',
            'PENDIENTE_COORDINACION',
            'PENDIENTE_ACADEMICA',
            'PENDIENTE_APROBACION_FINAL',
            'ABIERTO',
            true,
            true,
        ])->first();

        $nextSessions = EventSession::query()
            ->join('academic_events', 'academic_events.id', '=', 'event_sessions.academic_event_id')
            ->leftJoin('rooms', 'rooms.id', '=', 'event_sessions.room_id')
            ->leftJoin('zoom_accounts', 'zoom_accounts.id', '=', 'event_sessions.zoom_account_id')
            ->where('event_sessions.date', '>=', now()->toDateString())
            ->whereNull('academic_events.deleted_at')
            ->select([
                'event_sessions.*',
                DB::raw('coalesce(academic_events.short_name, academic_events.name) as event_name'),
                'rooms.name as room_name',
                'zoom_accounts.name as zoom_name',
            ])
            ->orderBy('event_sessions.date')
            ->orderBy('event_sessions.start_time')
            ->limit(8)
            ->get();

        $saturationBySpecialty = AcademicEvent::query()
            ->join('specialties', 'specialties.id', '=', 'academic_events.specialty_id')
            ->select('academic_events.specialty_id', 'specialties.name as specialty_name', DB::raw('count(*) as total'))
            ->groupBy('academic_events.specialty_id', 'specialties.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $criticalConflicts = SchedulingConflict::query()
            ->join('academic_events', 'academic_events.id', '=', 'scheduling_conflicts.academic_event_id')
            ->whereIn('scheduling_conflicts.severity', ['CRITICO', 'BLOQUEANTE'])
            ->where('scheduling_conflicts.status', 'ABIERTO')
            ->select('scheduling_conflicts.*', 'academic_events.name as event_name')
            ->latest('scheduling_conflicts.created_at')
            ->limit(6)
            ->get();

        return view('dashboard.index', compact('metrics', 'nextSessions', 'saturationBySpecialty', 'criticalConflicts'));
    }
}
