<x-layouts.app heading="Calendario academico" subheading="Vista mensual de sesiones tentativas, aprobadas y observadas.">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <a class="btn btn-secondary" href="{{ route('calendar.index', ['month' => $current->subMonth()->format('Y-m-01')]) }}">Mes anterior</a>
        <h2 class="text-xl font-medium">{{ ucfirst($current->translatedFormat('F Y')) }}</h2>
        <a class="btn btn-secondary" href="{{ route('calendar.index', ['month' => $current->addMonth()->format('Y-m-01')]) }}">Mes siguiente</a>
    </div>

    <div class="grid grid-cols-7 gap-2 text-center text-sm" style="color: var(--muted)">
        @foreach (['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'] as $day)
            <div class="py-2">{{ $day }}</div>
        @endforeach
    </div>

    <div class="grid grid-cols-7 gap-2">
        @foreach ($days as $day)
            <div class="card min-h-36 p-3 shadow-none {{ $day->month !== $current->month ? 'opacity-50' : '' }}">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium">{{ $day->day }}</span>
                    @if (($sessions[$day->toDateString()] ?? collect())->count() >= 3)
                        <span class="status-pill severity-ADVERTENCIA">Saturado</span>
                    @endif
                </div>
                <div class="mt-3 grid gap-2">
                    @foreach ($sessions[$day->toDateString()] ?? [] as $session)
                        @if ($session->academicEvent->launch_proposal_id)
                            <a class="rounded-lg p-2 text-left text-xs block" style="background: var(--accent-soft); color: var(--accent)" href="{{ route('launches.show', $session->academicEvent->launch_proposal_id) }}">
                                <span class="block font-medium">{{ $session->academicEvent->short_name ?: $session->academicEvent->name }}</span>
                                <span>{{ substr($session->start_time, 0, 5) }} · {{ $session->academicEvent->modality->name }}</span>
                            </a>
                        @else
                            <div class="rounded-lg p-2 text-left text-xs" style="background: var(--panel); color: var(--text); border: 1px solid var(--line)">
                                <span class="block font-medium">{{ $session->academicEvent->short_name ?: $session->academicEvent->name }}</span>
                                <span style="color: var(--muted)">{{ substr($session->start_time, 0, 5) }} · {{ $session->academicEvent->modality->name }} · Importado</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-layouts.app>
