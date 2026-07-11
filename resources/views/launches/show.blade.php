<x-layouts.app :heading="$launch->commercial_name" :subheading="$launch->code.' · '.$launch->specialty->name.' · '.$launch->audience->name">
    <div class="grid gap-5 xl:grid-cols-[1fr_360px]">
        @if ($hasBlockingConflicts)
            <div class="blocking-alert xl:col-span-2" role="alert">
                <div>
                    <p class="font-medium">Aprobacion bloqueada por cruce presencial</p>
                    <p class="mt-1 text-sm">Dos sesiones presenciales coinciden en fecha y horario. Edita la agenda y recalcula antes de continuar.</p>
                </div>
                <a class="btn btn-secondary" href="#editar-agenda">Editar agenda</a>
            </div>
        @endif

        <div class="grid gap-5">
            <section class="panel p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <x-status-pill>{{ $launch->status }}</x-status-pill>
                        <p class="mt-3 text-3xl font-medium">{{ $launch->score }}/100</p>
                        <p class="text-sm" style="color: var(--muted)">Score comercial ponderado</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @unless ($hasBlockingConflicts)
                            <a class="btn btn-secondary" href="#editar-agenda">Editar agenda</a>
                        @endunless
                        <form method="post" action="{{ route('launches.submit-approval', $launch) }}">
                            @csrf
                            <button class="btn btn-secondary" type="submit" @disabled($hasBlockingConflicts) title="{{ $hasBlockingConflicts ? 'Corrige el cruce presencial antes de enviar' : 'Enviar a aprobacion' }}">Enviar a aprobacion</button>
                        </form>
                        <form method="post" action="{{ route('launches.approve', $launch) }}">
                            @csrf
                            <button class="btn btn-primary" type="submit" @disabled($hasBlockingConflicts) title="{{ $hasBlockingConflicts ? 'Corrige el cruce presencial antes de aprobar' : 'Aprobar lanzamiento' }}">Aprobar</button>
                        </form>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    <div><p class="text-sm" style="color: var(--muted)">Tipo</p><p class="font-medium">{{ $launch->eventType->name }}</p></div>
                    <div><p class="text-sm" style="color: var(--muted)">Modalidad</p><p class="font-medium">{{ $launch->modality->name }}</p></div>
                    <div><p class="text-sm" style="color: var(--muted)">Prioridad</p><p class="font-medium">{{ $launch->priority }}</p></div>
                    <div><p class="text-sm" style="color: var(--muted)">Inicio</p><p class="font-medium">{{ $launch->academicEvent?->start_date?->format('d/m/Y') }}</p></div>
                    <div><p class="text-sm" style="color: var(--muted)">Termino</p><p class="font-medium">{{ $launch->academicEvent?->end_date?->format('d/m/Y') ?: 'Por calcular' }}</p></div>
                    <div><p class="text-sm" style="color: var(--muted)">Horas</p><p class="font-medium">{{ $launch->academicEvent?->total_hours }}</p></div>
                </div>
            </section>

            <section class="panel p-5">
                <h2 class="text-xl font-medium">Sesiones generadas</h2>
                <div class="mt-5 overflow-x-auto">
                    <table class="data-table w-full text-left text-sm">
                        <thead style="color: var(--muted)">
                        <tr>
                            <th class="py-2">Sesion</th>
                            <th>Fecha</th>
                            <th>Horario</th>
                            <th>Modalidad</th>
                            <th>Recurso</th>
                            <th>Estado</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($launch->academicEvent->sessions as $session)
                            <tr class="border-t" style="border-color: var(--line)">
                                <td class="py-3">{{ $session->title }}</td>
                                <td>{{ $session->date->format('d/m/Y') }}</td>
                                <td>{{ substr($session->start_time, 0, 5) }} - {{ substr($session->end_time, 0, 5) }}</td>
                                <td>{{ $session->modality->name }}</td>
                                <td>{{ $session->room?->name ?: $session->zoomAccount?->name ?: 'Por asignar' }}</td>
                                <td><x-status-pill>{{ $session->is_holiday ? 'FERIADO' : $session->status }}</x-status-pill></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel p-5 scroll-mt-6" id="editar-agenda">
                <h2 class="text-xl font-medium">Editar agenda</h2>
                <p class="mt-1 text-sm" style="color: var(--muted)">Ajusta fecha, horario y recursos. Al guardar se regeneran las sesiones y se validan los cruces.</p>
                <form class="mt-5 grid gap-4 md:grid-cols-4" method="post" action="{{ route('launches.regenerate-sessions', $launch) }}">
                    @csrf
                    <input class="input" name="start_date" type="date" value="{{ $launch->academicEvent->start_date?->toDateString() }}" required>
                    <input class="input" name="start_time" type="time" value="10:00" required>
                    <select class="select" name="frequency_type" required>
                        <option value="monthly">Mensual</option>
                        <option value="biweekly">Quincenal</option>
                        <option value="weekly">Semanal</option>
                    </select>
                    <input class="input" name="class_duration_minutes" type="number" min="60" max="480" value="{{ $launch->academicEvent->structure->class_duration_minutes }}" required>
                    <select class="select" name="room_id">
                        <option value="">Aula no aplica</option>
                        @foreach ($rooms as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                    <select class="select" name="zoom_account_id">
                        <option value="">Zoom no aplica</option>
                        @foreach ($zoomAccounts as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                    <select class="select" name="speaker_id">
                        <option value="">Docente por asignar</option>
                        @foreach ($speakers as $item)
                            <option value="{{ $item->id }}">{{ $item->full_name }}</option>
                        @endforeach
                    </select>
                    <div class="flex flex-wrap items-center gap-3 text-sm">
                        @foreach ([5 => 'Vie', 6 => 'Sab', 7 => 'Dom'] as $value => $label)
                            <label class="flex items-center gap-2"><input name="allowed_weekdays[]" type="checkbox" value="{{ $value }}" @checked($value === 6)> {{ $label }}</label>
                        @endforeach
                    </div>
                    <button class="btn btn-primary md:col-span-4" type="submit">Regenerar y recalcular conflictos</button>
                </form>
            </section>
        </div>

        <aside class="grid gap-5 content-start">
            <section class="panel p-5">
                <h2 class="text-xl font-medium">Conflictos</h2>
                <div class="mt-4 grid gap-3">
                    @forelse ($launch->academicEvent->conflicts->where('status', 'ABIERTO') as $conflict)
                        <div class="card p-4 shadow-none">
                            <x-status-pill class="severity-{{ $conflict->severity }}">{{ $conflict->severity }}</x-status-pill>
                            <p class="mt-2 text-sm">{{ $conflict->message }}</p>
                            <p class="mt-1 text-xs" style="color: var(--muted)">{{ $conflict->recommendation }}</p>
                        </div>
                    @empty
                        <p class="text-sm" style="color: var(--muted)">Sin conflictos abiertos.</p>
                    @endforelse
                </div>
            </section>

            <section class="panel p-5">
                <h2 class="text-xl font-medium">Timeline de aprobacion</h2>
                <div class="mt-4 grid gap-3">
                    @forelse ($launch->approvalRequests as $request)
                        @foreach ($request->logs as $log)
                            <div class="border-l-2 pl-3" style="border-color: var(--accent)">
                                <p class="text-sm font-medium">{{ $log->action }}</p>
                                <p class="text-xs" style="color: var(--muted)">{{ $log->created_at->format('d/m/Y H:i') }}</p>
                                <p class="mt-1 text-sm">{{ $log->comment }}</p>
                            </div>
                        @endforeach
                    @empty
                        <p class="text-sm" style="color: var(--muted)">Aun no se envio a aprobacion.</p>
                    @endforelse
                </div>
            </section>

            <section class="panel p-5">
                <h2 class="text-xl font-medium">Correo formal</h2>
                <p class="mt-3 text-sm font-medium">{{ $emailPreview['subject'] }}</p>
                <pre class="email-preview mt-3 whitespace-pre-wrap rounded-xl p-4 text-xs" style="background: var(--panel); color: var(--muted)">{{ $emailPreview['body'] }}</pre>
            </section>
        </aside>
    </div>
</x-layouts.app>
