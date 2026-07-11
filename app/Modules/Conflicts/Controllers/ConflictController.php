<?php

namespace App\Modules\Conflicts\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SchedulingConflict;
use Illuminate\Http\Request;

class ConflictController extends Controller
{
    public function index()
    {
        $conflicts = SchedulingConflict::query()
            ->with(['academicEvent', 'session.room', 'session.zoomAccount', 'rule'])
            ->where('status', 'ABIERTO')
            ->latest()
            ->paginate(15);

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
