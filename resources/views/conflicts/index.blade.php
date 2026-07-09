<x-layouts.app heading="Panel de conflictos" subheading="Reglas incumplidas, severidad, recurso afectado y accion recomendada.">
    <div class="grid gap-4">
        @forelse ($conflicts as $conflict)
            <article class="panel p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <x-status-pill class="severity-{{ $conflict->severity }}">{{ $conflict->severity }}</x-status-pill>
                        <h2 class="mt-3 text-xl font-medium">{{ $conflict->academicEvent->name }}</h2>
                        <p class="mt-1 text-sm" style="color: var(--muted)">{{ $conflict->message }}</p>
                    </div>
                    <form class="flex gap-2" method="post" action="{{ route('conflicts.resolve', $conflict) }}">
                        @csrf
                        <input class="input w-64" name="resolution_notes" placeholder="Nota de solucion" required>
                        <button class="btn btn-primary" type="submit">Resolver</button>
                    </form>
                </div>
                <div class="mt-4 grid gap-3 md:grid-cols-4 text-sm">
                    <div><p style="color: var(--muted)">Fecha</p><p>{{ $conflict->session?->date?->format('d/m/Y') }}</p></div>
                    <div><p style="color: var(--muted)">Horario</p><p>{{ $conflict->session ? substr($conflict->session->start_time, 0, 5).' - '.substr($conflict->session->end_time, 0, 5) : '-' }}</p></div>
                    <div><p style="color: var(--muted)">Regla</p><p>{{ $conflict->rule?->name ?: 'Regla operativa' }}</p></div>
                    <div><p style="color: var(--muted)">Recomendacion</p><p>{{ $conflict->recommendation }}</p></div>
                </div>
            </article>
        @empty
            <div class="panel p-8 text-center" style="color: var(--muted)">No hay conflictos abiertos.</div>
        @endforelse
    </div>

    <div class="mt-5">{{ $conflicts->links() }}</div>
</x-layouts.app>
