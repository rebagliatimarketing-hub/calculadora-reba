<x-layouts.app heading="Reporte mensual" subheading="Resumen ejecutivo por modalidad, especialidad y severidad de conflicto.">
    <div class="grid gap-5 xl:grid-cols-3">
        <section class="panel p-5">
            <h2 class="text-xl font-medium">Modalidad</h2>
            <div class="mt-4 grid gap-3">
                @foreach ($eventsByModality as $item)
                    <div class="flex justify-between text-sm"><span>{{ $item->modality_name }}</span><span>{{ $item->total }}</span></div>
                @endforeach
            </div>
        </section>

        <section class="panel p-5">
            <h2 class="text-xl font-medium">Especialidad</h2>
            <div class="mt-4 grid gap-3">
                @foreach ($eventsBySpecialty as $item)
                    <div class="flex justify-between text-sm"><span>{{ $item->specialty_name }}</span><span>{{ $item->total }}</span></div>
                @endforeach
            </div>
        </section>

        <section class="panel p-5">
            <h2 class="text-xl font-medium">Conflictos</h2>
            <div class="mt-4 grid gap-3">
                @forelse ($conflictsBySeverity as $item)
                    <div class="flex justify-between text-sm"><span>{{ $item->severity }}</span><span>{{ $item->total }}</span></div>
                @empty
                    <p class="text-sm" style="color: var(--muted)">Sin conflictos registrados.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.app>
