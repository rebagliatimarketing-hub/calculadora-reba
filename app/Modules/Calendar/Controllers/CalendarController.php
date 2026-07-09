<?php

namespace App\Modules\Calendar\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EventSession;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $current = CarbonImmutable::parse($request->input('month', now()->format('Y-m-01')))->startOfMonth();
        $start = $current->startOfWeek();
        $end = $current->endOfMonth()->endOfWeek();

        $sessions = EventSession::query()
            ->with(['academicEvent.modality', 'academicEvent.specialty', 'room', 'zoomAccount'])
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->orderBy('start_time')
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
            ->with(['academicEvent.modality', 'room', 'zoomAccount'])
            ->when($request->filled('start'), fn ($query) => $query->whereDate('date', '>=', $request->start))
            ->when($request->filled('end'), fn ($query) => $query->whereDate('date', '<=', $request->end))
            ->get();

        return response()->json($sessions->map(fn (EventSession $session) => [
            'id' => $session->id,
            'title' => $session->academicEvent->short_name ?: $session->academicEvent->name,
            'date' => $session->date->toDateString(),
            'start_time' => substr($session->start_time, 0, 5),
            'end_time' => substr($session->end_time, 0, 5),
            'modality' => $session->academicEvent->modality->name,
            'resource' => $session->room?->name ?: $session->zoomAccount?->name,
        ]));
    }
}
