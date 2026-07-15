<x-layouts.app heading="Aprobaciones" subheading="Linea de tiempo de solicitudes enviadas, aprobadas u observadas.">
    <div class="grid gap-4">
        @forelse ($requests as $request)
            <article class="panel p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <x-status-pill>{{ $request->status }}</x-status-pill>
                        <h2 class="mt-3 text-xl font-medium">{{ $request->approvable_name ?: 'Solicitud' }}</h2>
                        <p class="text-sm" style="color: var(--muted)">{{ $request->workflow_name }}</p>
                    </div>
                    <p class="text-sm" style="color: var(--muted)">{{ $request->requested_at?->format('d/m/Y H:i') }}</p>
                </div>
                <div class="mt-5 grid gap-3">
                    @foreach ($request->logs as $log)
                        <div class="border-l-2 pl-3" style="border-color: var(--accent)">
                            <p class="text-sm font-medium">{{ $log->action }}</p>
                            <p class="text-sm" style="color: var(--muted)">{{ $log->comment ?: 'Sin comentario' }}</p>
                        </div>
                    @endforeach
                </div>
            </article>
        @empty
            <div class="panel p-8 text-center" style="color: var(--muted)">No hay solicitudes de aprobacion todavia.</div>
        @endforelse
    </div>

    <div class="mt-5">{{ $requests->links() }}</div>
</x-layouts.app>
