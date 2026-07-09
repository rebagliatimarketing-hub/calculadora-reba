<x-layouts.app heading="Nuevo lanzamiento" subheading="Formulario por pasos para crear propuesta, calcular score, generar fechas y validar conflictos.">
    <form class="grid gap-5" method="post" action="{{ route('launches.store') }}">
        @csrf

        <section class="panel p-5">
            <h2 class="text-xl font-medium">1. Datos generales</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="text-sm font-medium">Nombre tentativo</label>
                    <input class="input mt-2" name="tentative_name" value="{{ old('tentative_name') }}" required>
                </div>
                <div>
                    <label class="text-sm font-medium">Nombre comercial</label>
                    <input class="input mt-2" name="commercial_name" value="{{ old('commercial_name') }}" required>
                </div>
                <div>
                    <label class="text-sm font-medium">Tipo de evento</label>
                    <select class="select mt-2" name="event_type_id" required>
                        @foreach ($eventTypes as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium">Modalidad</label>
                    <select class="select mt-2" name="modality_id" required>
                        @foreach ($modalities as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium">Especialidad</label>
                    <select class="select mt-2" name="specialty_id" required>
                        @foreach ($specialties as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium">Publico objetivo</label>
                    <select class="select mt-2" name="audience_segment_id" required>
                        @foreach ($audiences as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium">Certificacion</label>
                    <select class="select mt-2" name="certification_entity_id">
                        <option value="">Sin certificacion definida</option>
                        @foreach ($certifications as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium">Prioridad</label>
                    <select class="select mt-2" name="priority" required>
                        @foreach (['BAJA', 'MEDIA', 'ALTA', 'CRITICA'] as $priority)
                            <option value="{{ $priority }}">{{ $priority }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        <section class="panel p-5">
            <h2 class="text-xl font-medium">2. Evaluacion comercial</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-3">
                @foreach ([
                    'market_demand' => 'Demanda mercado',
                    'survey_interest' => 'Interes encuesta',
                    'specialty_recurrence' => 'Recurrencia especialidad',
                    'commercial_opportunity' => 'Oportunidad comercial',
                    'operational_ease' => 'Facilidad operativa',
                    'differentiation' => 'Diferenciacion',
                ] as $field => $label)
                    <div>
                        <label class="text-sm font-medium">{{ $label }}</label>
                        <input class="input mt-2" name="{{ $field }}" type="number" min="0" max="100" value="{{ old($field, 70) }}" required>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="text-sm font-medium">Fuente principal</label>
                    <select class="select mt-2" name="source_type" required>
                        @foreach (['WhatsApp', 'Competencia', 'Organico', 'Ventas', 'Pauta breve', 'Historico'] as $source)
                            <option value="{{ $source }}">{{ $source }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium">Interesados registrados</label>
                    <input class="input mt-2" name="interested_count" type="number" min="0" value="{{ old('interested_count', 0) }}">
                </div>
                <div>
                    <label class="text-sm font-medium">Descripcion del publico</label>
                    <textarea class="textarea mt-2" name="target_description" rows="4">{{ old('target_description') }}</textarea>
                </div>
                <div>
                    <label class="text-sm font-medium">Justificacion comercial</label>
                    <textarea class="textarea mt-2" name="commercial_justification" rows="4">{{ old('commercial_justification') }}</textarea>
                </div>
            </div>
        </section>

        <section class="panel p-5">
            <h2 class="text-xl font-medium">3. Estructura academica</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-4">
                <div>
                    <label class="text-sm font-medium">Duracion meses</label>
                    <input class="input mt-2" name="duration_months" type="number" min="1" max="12" value="6" required>
                </div>
                <div>
                    <label class="text-sm font-medium">Modulos</label>
                    <input class="input mt-2" name="modules_count" type="number" min="1" max="24" value="6" required>
                </div>
                <div>
                    <label class="text-sm font-medium">Clases totales</label>
                    <input class="input mt-2" name="classes_count" type="number" min="1" max="48" value="6" required>
                </div>
                <div>
                    <label class="text-sm font-medium">Clases por mes</label>
                    <input class="input mt-2" name="classes_per_month" type="number" min="1" max="8" value="1" required>
                </div>
                <div>
                    <label class="text-sm font-medium">Frecuencia</label>
                    <select class="select mt-2" name="frequency_type" required>
                        <option value="monthly">Mensual</option>
                        <option value="biweekly">Quincenal</option>
                        <option value="weekly">Semanal</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium">Minutos por clase</label>
                    <input class="input mt-2" name="class_duration_minutes" type="number" min="60" max="480" value="240" required>
                </div>
                <label class="mt-8 flex items-center gap-2 text-sm"><input name="has_workshops" type="checkbox" value="1"> Taller incluido</label>
                <label class="mt-8 flex items-center gap-2 text-sm"><input name="has_virtual_workshops" type="checkbox" value="1"> Taller virtual</label>
            </div>
            <div class="mt-4">
                <label class="text-sm font-medium">Justificacion academica</label>
                <textarea class="textarea mt-2" name="academic_justification" rows="3">{{ old('academic_justification') }}</textarea>
            </div>
        </section>

        <section class="panel p-5">
            <h2 class="text-xl font-medium">4. Fechas y recursos</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-3">
                <div>
                    <label class="text-sm font-medium">Fecha tentativa</label>
                    <input class="input mt-2" name="start_date" type="date" value="{{ now()->addMonth()->next('Saturday')->toDateString() }}" required>
                </div>
                <div>
                    <label class="text-sm font-medium">Hora de inicio</label>
                    <input class="input mt-2" name="start_time" type="time" value="10:00" required>
                </div>
                <div>
                    <label class="text-sm font-medium">Docente</label>
                    <select class="select mt-2" name="speaker_id">
                        <option value="">Por asignar</option>
                        @foreach ($speakers as $item)
                            <option value="{{ $item->id }}">{{ $item->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium">Aula</label>
                    <select class="select mt-2" name="room_id">
                        <option value="">No aplica</option>
                        @foreach ($rooms as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium">Cuenta Zoom</label>
                    <select class="select mt-2" name="zoom_account_id">
                        <option value="">No aplica</option>
                        @foreach ($zoomAccounts as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium">Dias permitidos</label>
                    <div class="mt-3 flex flex-wrap gap-3 text-sm">
                        @foreach ([5 => 'Vie', 6 => 'Sab', 7 => 'Dom'] as $value => $label)
                            <label class="flex items-center gap-2"><input name="allowed_weekdays[]" type="checkbox" value="{{ $value }}" @checked($value === 6)> {{ $label }}</label>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <div class="flex justify-end gap-3">
            <a class="btn btn-secondary" href="{{ route('launches.index') }}">Cancelar</a>
            <button class="btn btn-primary" type="submit">Crear y validar</button>
        </div>
    </form>
</x-layouts.app>
