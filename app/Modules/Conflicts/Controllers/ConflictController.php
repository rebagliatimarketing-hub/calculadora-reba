<?php

namespace App\Modules\Conflicts\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SchedulingConflict;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class ConflictController extends Controller
{
    public function index()
    {
        $conflicts = SchedulingConflict::query()
            ->join('academic_events', 'academic_events.id', '=', 'scheduling_conflicts.academic_event_id')
            ->leftJoin('event_sessions', 'event_sessions.id', '=', 'scheduling_conflicts.event_session_id')
            ->leftJoin('scheduling_rules', 'scheduling_rules.id', '=', 'scheduling_conflicts.rule_id')
            ->where('scheduling_conflicts.status', 'ABIERTO')
            ->select([
                'scheduling_conflicts.*',
                'academic_events.name as event_name',
                'event_sessions.date as session_date',
                'event_sessions.start_time as session_start_time',
                'event_sessions.end_time as session_end_time',
                'scheduling_rules.name as rule_name',
            ])
            ->latest('scheduling_conflicts.created_at')
            ->paginate(15);

        $conflicts->getCollection()->each(function (SchedulingConflict $conflict): void {
            $conflict->session_date = $conflict->session_date
                ? CarbonImmutable::parse($conflict->session_date)
                : null;
        });

        return view('conflicts.index', compact('conflicts'));
    }

    public function resolve(SchedulingConflict $conflict, Request $request)
    {
        $data = $request->validate([
            'resolution_notes' => ['required', 'string', 'max:1000'],
        ]);

        $conflict->update([
            'status' => 'RESUELTO',
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
            'resolution_notes' => $data['resolution_notes'],
        ]);

        $event = $conflict->academicEvent;

        if (! $event->conflicts()->where('status', 'ABIERTO')->exists()) {
            $event->update(['status' => 'TENTATIVO']);
            $event->launchProposal?->update(['status' => 'PENDIENTE_COORDINACION']);
        }

        return back()->with('status', 'Conflicto marcado como resuelto.');
    }
}
