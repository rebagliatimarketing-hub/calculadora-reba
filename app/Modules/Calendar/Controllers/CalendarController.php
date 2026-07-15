<?php

namespace App\Modules\Calendar\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EventSession;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $current = CarbonImmutable::parse($request->input('month', now()->format('Y-m-01')))->startOfMonth();
        $start = $current->startOfWeek();
        $end = $current->endOfMonth()->endOfWeek();

        $sessions = EventSession::query()
            ->join('academic_events', 'academic_events.id', '=', 'event_sessions.academic_event_id')
            ->join('modalities', 'modalities.id', '=', 'event_sessions.modality_id')
            ->whereBetween('event_sessions.date', [$start->toDateString(), $end->toDateString()])
            ->whereNull('academic_events.deleted_at')
            ->select([
                'event_sessions.*',
                'academic_events.launch_proposal_id',
                DB::raw('coalesce(academic_events.short_name, academic_events.name) as event_name'),
                'modalities.name as modality_name',
            ])
            ->orderBy('event_sessions.date')
            ->orderBy('event_sessions.start_time')
            ->get()
            ->groupBy(fn (EventSession $session) => $session->date->toDateString());

        return view('calendar.index', [
            'current' => $current,
            'days' => collect(range(0, $start->diffInDays($end)))->map(fn (int $offset) => $start->addDays($offset)),
            'sessions' => $sessions,
        ]);
    }

    public function events(Request $request)
    {
        $sessions = EventSession::query()
            ->join('academic_events', 'academic_events.id', '=', 'event_sessions.academic_event_id')
            ->join('modalities', 'modalities.id', '=', 'event_sessions.modality_id')
            ->leftJoin('rooms', 'rooms.id', '=', 'event_sessions.room_id')
            ->leftJoin('zoom_accounts', 'zoom_accounts.id', '=', 'event_sessions.zoom_account_id')
            ->when($request->filled('start'), fn ($query) => $query->where('event_sessions.date', '>=', $request->start))
            ->when($request->filled('end'), fn ($query) => $query->where('event_sessions.date', '<=', $request->end))
            ->whereNull('academic_events.deleted_at')
            ->select([
                'event_sessions.*',
                DB::raw('coalesce(academic_events.short_name, academic_events.name) as event_name'),
                'modalities.name as modality_name',
                'rooms.name as room_name',
                'zoom_accounts.name as zoom_name',
            ])
            ->orderBy('event_sessions.date')
            ->orderBy('event_sessions.start_time')
            ->get();

        return response()->json($sessions->map(fn (EventSession $session) => [
            'id' => $session->id,
            'title' => $session->event_name,
            'date' => $session->date->toDateString(),
            'start_time' => substr($session->start_time, 0, 5),
            'end_time' => substr($session->end_time, 0, 5),
            'modality' => $session->modality_name,
            'resource' => $session->room_name ?: $session->zoom_name,
        ]));
    }
}
