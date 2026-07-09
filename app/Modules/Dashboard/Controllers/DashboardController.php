<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicEvent;
use App\Models\EventSession;
use App\Models\LaunchProposal;
use App\Models\SchedulingConflict;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $month = now()->month;
        $year = now()->year;

        $metrics = [
            'total' => LaunchProposal::query()->whereHas('cycle', fn ($query) => $query->where('month', $month)->where('year', $year))->count(),
            'approved' => LaunchProposal::query()->where('status', 'APROBADO_FINAL')->count(),
            'review' => LaunchProposal::query()->whereIn('status', ['PENDIENTE_COORDINACION', 'PENDIENTE_ACADEMICA', 'PENDIENTE_APROBACION_FINAL'])->count(),
            'conflicts' => SchedulingConflict::query()->where('status', 'ABIERTO')->count(),
            'presential' => AcademicEvent::query()->whereHas('modality', fn ($query) => $query->where('requires_room', true))->count(),
            'virtual' => AcademicEvent::query()->whereHas('modality', fn ($query) => $query->where('requires_zoom', true))->count(),
        ];

        $nextSessions = EventSession::query()
            ->with(['academicEvent', 'room', 'zoomAccount'])
            ->whereDate('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->limit(8)
            ->get();

        $saturationBySpecialty = AcademicEvent::query()
            ->select('specialty_id', DB::raw('count(*) as total'))
            ->with('specialty')
            ->groupBy('specialty_id')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $criticalConflicts = SchedulingConflict::query()
            ->with(['academicEvent', 'session'])
            ->whereIn('severity', ['CRITICO', 'BLOQUEANTE'])
            ->where('status', 'ABIERTO')
            ->latest()
            ->limit(6)
            ->get();

        return view('dashboard.index', compact('metrics', 'nextSessions', 'saturationBySpecialty', 'criticalConflicts'));
    }
}
