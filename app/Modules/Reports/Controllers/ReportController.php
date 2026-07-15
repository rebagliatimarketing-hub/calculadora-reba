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
            ->join('modalities', 'modalities.id', '=', 'academic_events.modality_id')
            ->select('academic_events.modality_id', 'modalities.name as modality_name', DB::raw('count(*) as total'))
            ->groupBy('academic_events.modality_id', 'modalities.name')
            ->get();

        $eventsBySpecialty = AcademicEvent::query()
            ->join('specialties', 'specialties.id', '=', 'academic_events.specialty_id')
            ->select('academic_events.specialty_id', 'specialties.name as specialty_name', DB::raw('count(*) as total'))
            ->groupBy('academic_events.specialty_id', 'specialties.name')
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
