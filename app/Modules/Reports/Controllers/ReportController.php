<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicEvent;
use App\Models\SchedulingConflict;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function monthly()
    {
        $eventsByModality = AcademicEvent::query()
            ->select('modality_id', DB::raw('count(*) as total'))
            ->with('modality')
            ->groupBy('modality_id')
            ->get();

        $eventsBySpecialty = AcademicEvent::query()
            ->select('specialty_id', DB::raw('count(*) as total'))
            ->with('specialty')
            ->groupBy('specialty_id')
            ->orderByDesc('total')
            ->get();

        $conflictsBySeverity = SchedulingConflict::query()
            ->select('severity', DB::raw('count(*) as total'))
            ->groupBy('severity')
            ->orderByDesc('total')
            ->get();

        return view('reports.monthly', compact('eventsByModality', 'eventsBySpecialty', 'conflictsBySeverity'));
    }
}
