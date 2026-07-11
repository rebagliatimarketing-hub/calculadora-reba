<x-layouts.app heading="Dashboard de lanzamientos" subheading="Lectura rapida de aprobaciones, conflictos, saturacion y proximos inicios.">
    <section class="grid grid-cols-2 gap-4 lg:grid-cols-3 xl:grid-cols-6">
        <x-metric-card label="Eventos del mes" :value="$metrics['total']" />
        <x-metric-card label="Aprobados" :value="$metrics['approved']" />
        <x-metric-card label="En revision" :value="$metrics['review']" />
        <x-metric-card label="Conflictos" :value="$metrics['conflicts']" />
        <x-metric-card label="Presenciales" :value="$metrics['presential']" />
        <x-metric-card label="Virtuales" :value="$metrics['virtual']" />
    </section>

    <section class="mt-6 grid gap-5 xl:grid-cols-[1.3fr_.7fr]">
        <div class="panel p-5">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-medium">Proximas sesiones</h2>
                <a class="btn btn-secondary" href="{{ route('calendar.index') }}">Ver calendario</a>
            </div>
            <div class="mt-5 overflow-x-auto">
                <table class="data-table w-full text-left text-sm">
                    <thead style="color: var(--muted)">
                    <tr>
                        <th class="py-2">Fecha</th>
                        <th>Evento</th>
                        <th>Horario</th>
                        <th>Recurso</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($nextSessions as $session)
                        <tr class="border-t" style="border-color: var(--line)">
                            <td class="cell-nowrap">{{ $session->date->format('d/m/Y') }}</td>
                            <td>{{ $session->academicEvent->short_name ?: $session->academicEvent->name }}</td>
                            <td class="cell-nowrap">{{ substr($session->start_time, 0, 5) }} - {{ substr($session->end_time, 0, 5) }}</td>
                            <td>{{ $session->room?->name ?: $session->zoomAccount?->name ?: 'Por asignar' }}</td>
                        </tr>
                    @empty
                        <tr><td class="py-6" colspan="4" style="color: var(--muted)">No hay sesiones proximas.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid gap-5">
            <div class="panel p-5">
                <h2 class="text-xl font-medium">Saturacion por especialidad</h2>
                <div class="mt-4 grid gap-3">
                    @foreach ($saturationBySpecialty as $item)
                        <div>
                            <div class="flex justify-between text-sm">
                                <span>{{ $item->specialty->name }}</span>
                                <span>{{ $item->total }}</span>
                            </div>
                            <div class="mt-2 h-2 rounded-full" style="background: var(--accent-soft)">
                                <div class="h-2 rounded-full" style="width: {{ min(100, $item->total * 18) }}%; background: var(--accent)"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="panel p-5">
                <h2 class="text-xl font-medium">Alertas criticas</h2>
                <div class="mt-4 grid gap-3">
                    @forelse ($criticalConflicts as $conflict)
                        <div class="card p-4 shadow-none">
                            <x-status-pill class="severity-{{ $conflict->severity }}">{{ $conflict->severity }}</x-status-pill>
                            <p class="mt-2 text-sm">{{ $conflict->message }}</p>
                            <p class="mt-1 text-xs" style="color: var(--muted)">{{ $conflict->academicEvent->name }}</p>
                        </div>
                    @empty
                        <p class="text-sm" style="color: var(--muted)">No hay conflictos criticos abiertos.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
