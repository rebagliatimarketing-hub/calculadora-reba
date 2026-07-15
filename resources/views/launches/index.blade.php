<x-layouts.app heading="Lanzamientos" subheading="Tabla operativa para revisar propuestas, score comercial, estado y conflictos.">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <form class="flex flex-wrap gap-2" method="get">
            <select class="select w-52" name="status">
                <option value="">Todos los estados</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', $status) }}</option>
                @endforeach
            </select>
            <input class="input w-32" name="month" type="number" min="1" max="12" value="{{ request('month') }}" placeholder="Mes">
            <button class="btn btn-secondary" type="submit">Filtrar</button>
        </form>
        <a class="btn btn-primary" href="{{ route('launches.create') }}">Nuevo lanzamiento</a>
    </div>

    <div class="panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table data-table-wide w-full text-left text-sm">
                <thead style="background: var(--panel); color: var(--muted)">
                <tr>
                    <th class="p-4">Codigo</th>
                    <th>Evento</th>
                    <th>Tipo</th>
                    <th>Modalidad</th>
                    <th>Especialidad</th>
                    <th>Score</th>
                    <th>Conflictos</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($launches as $launch)
                    <tr class="border-t" style="border-color: var(--line)">
                        <td class="p-4">{{ $launch->code }}</td>
                        <td>{{ $launch->commercial_name }}</td>
                        <td>{{ $launch->event_type_name }}</td>
                        <td>{{ $launch->modality_name }}</td>
                        <td>{{ $launch->specialty_name }}</td>
                        <td>{{ $launch->score }}/100</td>
                        <td>{{ $launch->open_conflicts_count }}</td>
                        <td><x-status-pill>{{ $launch->status }}</x-status-pill></td>
                        <td class="pr-4"><a class="btn btn-secondary" href="{{ route('launches.show', $launch) }}">Gestionar</a></td>
                    </tr>
                @empty
                    <tr><td class="p-8" colspan="9" style="color: var(--muted)">No hay lanzamientos con estos filtros.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">{{ $launches->links() }}</div>
</x-layouts.app>
