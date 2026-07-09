<?php

namespace Database\Seeders;

use App\Models\AcademicEvent;
use App\Models\ApprovalStep;
use App\Models\ApprovalWorkflow;
use App\Models\AudienceSegment;
use App\Models\CertificationEntity;
use App\Models\Department;
use App\Models\EventAcademicStructure;
use App\Models\EventType;
use App\Models\Holiday;
use App\Models\LaunchCycle;
use App\Models\LaunchProposal;
use App\Models\LaunchResearchSource;
use App\Models\Modality;
use App\Models\Role;
use App\Models\Room;
use App\Models\SchedulingRule;
use App\Models\Speaker;
use App\Models\Specialty;
use App\Models\User;
use App\Models\ZoomAccount;
use App\Modules\AcademicEvents\Services\EventScheduleGenerator;
use App\Modules\Scheduling\Services\ConflictDetector;
use App\Shared\DTOs\ScheduleGenerationOptions;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $departments = collect(['Marketing', 'Coordinacion Academica', 'Academica', 'Ventas', 'Gerencia', 'Trafico Digital'])
            ->mapWithKeys(fn (string $name) => [Str::slug($name) => Department::query()->firstOrCreate([
                'slug' => Str::slug($name),
            ], [
                'name' => $name,
            ])]);

        $roles = collect([
            'super-admin' => ['Super Admin', 'Control total del sistema.'],
            'jefatura-marketing' => ['Jefatura de Marketing', 'Crea y valida ciclos de lanzamiento.'],
            'coordinacion-academica' => ['Coordinacion Academica', 'Valida estructura academica y fechas.'],
            'gerencia' => ['Gerencia', 'Revisa aprobaciones finales.'],
        ])->mapWithKeys(fn (array $data, string $slug) => [$slug => Role::query()->firstOrCreate([
            'slug' => $slug,
        ], [
            'name' => $data[0],
            'description' => $data[1],
        ])]);

        $admin = User::query()->updateOrCreate([
            'email' => env('ADMIN_EMAIL', 'admin@rebagliati.edu.pe'),
        ], [
            'name' => env('ADMIN_NAME', 'Admin REBA'),
            'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
            'department_id' => $departments['marketing']->id,
            'role_id' => $roles['super-admin']->id,
            'is_active' => true,
        ]);

        $specialties = collect(['Enfermeria', 'Tecnicos en Enfermeria', 'Obstetricia', 'Medicina', 'Fisioterapia', 'Farmacia', 'Laboratorio Clinico', 'Radiologia', 'Psicologia', 'Odontologia', 'Sin clasificar'])
            ->map(fn (string $name) => Specialty::query()->firstOrCreate([
                'slug' => Str::slug($name),
            ], [
                'name' => $name,
            ]));

        $audiences = collect([
            ['Profesionales de salud', 'Profesionales titulados o bachilleres', 'Salud', 5],
            ['Tecnicos de enfermeria', 'Tecnicos que requieren talleres y fines de semana', 'Salud tecnica', 4],
            ['Publico general', 'Personas interesadas en formacion corta', 'General', 2],
        ])->map(fn (array $item) => AudienceSegment::query()->firstOrCreate(['name' => $item[0]], [
            'description' => $item[1],
            'career_area' => $item[2],
            'priority_level' => $item[3],
        ]));

        $eventTypes = collect([
            ['Diplomado', 'diplomado', 6],
            ['Diplomado intensivo', 'diplomado-intensivo', 3],
            ['Curso', 'curso', 1],
            ['Curso modular', 'curso-modular', 4],
            ['Taller', 'taller', 1],
            ['Webinar', 'webinar', 1],
        ])->map(fn (array $item) => EventType::query()->firstOrCreate(['slug' => $item[1]], [
            'name' => $item[0],
            'default_duration_months' => $item[2],
        ]));

        $modalities = collect([
            ['Presencial', 'presencial', true, false, false],
            ['Virtual', 'virtual', false, true, false],
            ['Semipresencial', 'semipresencial', true, true, false],
            ['Hibrido', 'hibrido', true, true, false],
            ['Asincronico', 'asincronico', false, false, true],
        ])->map(fn (array $item) => Modality::query()->firstOrCreate(['slug' => $item[1]], [
            'name' => $item[0],
            'requires_room' => $item[2],
            'requires_zoom' => $item[3],
            'is_async' => $item[4],
        ]));

        $certification = CertificationEntity::query()->firstOrCreate(['name' => 'Rebagliati Diplomados'], [
            'type' => 'Interna',
            'description' => 'Certificacion institucional para programas academicos.',
        ]);

        $room = Room::query()->firstOrCreate(['name' => 'Auditorio Principal'], [
            'location' => 'Lima',
            'floor' => '1',
            'capacity' => 80,
        ]);

        $zoom = ZoomAccount::query()->firstOrCreate(['email' => 'zoom1@rebagliati.edu.pe'], [
            'name' => 'Zoom Academico 1',
            'capacity' => 500,
        ]);

        $speaker = Speaker::query()->firstOrCreate(['full_name' => 'Dra. Maria Salazar'], [
            'profession' => 'Enfermera especialista',
            'specialty' => 'Enfermeria',
            'email' => 'maria.salazar@example.com',
        ]);

        collect([
            ['2026-07-28', 'Fiestas Patrias'],
            ['2026-07-29', 'Fiestas Patrias'],
            ['2026-08-30', 'Santa Rosa de Lima'],
            ['2026-10-08', 'Combate de Angamos'],
            ['2026-12-25', 'Navidad'],
        ])->each(fn (array $holiday) => Holiday::query()->firstOrCreate(['date' => $holiday[0]], ['name' => $holiday[1]]));

        collect([
            ['HOLIDAY_PRESENTIAL', 'No programar presencial en feriado', 'BLOQUEANTE', true],
            ['ROOM_OVERLAP', 'Cruce de aula', 'CRITICO', true],
            ['ZOOM_OVERLAP', 'Cruce de Zoom', 'CRITICO', true],
            ['SPEAKER_OVERLAP', 'Cruce de docente', 'ALTO', false],
            ['AUDIENCE_OVERLAP', 'Cruce de publico objetivo', 'ADVERTENCIA', false],
            ['WEAK_WEEKDAY', 'Dia de asistencia debil', 'ADVERTENCIA', false],
        ])->each(fn (array $rule) => SchedulingRule::query()->firstOrCreate(['code' => $rule[0]], [
            'name' => $rule[1],
            'description' => 'Regla base del motor de conflictos.',
            'severity' => $rule[2],
            'is_blocking' => $rule[3],
        ]));

        $workflow = ApprovalWorkflow::query()->firstOrCreate(['module' => 'launches'], [
            'name' => 'Aprobacion de lanzamientos',
            'is_active' => true,
        ]);

        collect([
            [1, $departments['coordinacion-academica']->id, $roles['coordinacion-academica']->id, 'Validar estructura academica'],
            [2, $departments['academica']->id, null, 'Validar tema y docente'],
            [3, $departments['gerencia']->id, $roles['gerencia']->id, 'Aprobacion final'],
        ])->each(fn (array $step) => ApprovalStep::query()->firstOrCreate([
            'workflow_id' => $workflow->id,
            'step_order' => $step[0],
        ], [
            'department_id' => $step[1],
            'role_id' => $step[2],
            'action_required' => $step[3],
        ]));

        $cycle = LaunchCycle::query()->firstOrCreate([
            'year' => 2026,
            'month' => 9,
        ], [
            'name' => 'Ciclo Setiembre 2026',
            'status' => 'ABIERTO',
            'opened_by' => $admin->id,
        ]);

        $launch = LaunchProposal::query()->firstOrCreate(['code' => 'REBA-202609-001'], [
            'launch_cycle_id' => $cycle->id,
            'tentative_name' => 'Diplomado en Gestion de Emergencias',
            'commercial_name' => 'Diplomado en Gestion de Emergencias y Urgencias',
            'final_name' => 'Diplomado en Gestion de Emergencias y Urgencias',
            'specialty_id' => $specialties->firstWhere('slug', 'enfermeria')->id,
            'audience_segment_id' => $audiences->firstWhere('name', 'Profesionales de salud')->id,
            'event_type_id' => $eventTypes->firstWhere('slug', 'diplomado')->id,
            'modality_id' => $modalities->firstWhere('slug', 'virtual')->id,
            'certification_entity_id' => $certification->id,
            'target_description' => 'Profesionales de salud que buscan fortalecer respuesta ante emergencias.',
            'commercial_justification' => 'Alta demanda en consultas de WhatsApp y pauta historica.',
            'academic_justification' => 'Tema recurrente para enfermeria y medicina.',
            'duration_months' => 6,
            'classes_per_month' => 1,
            'priority' => 'ALTA',
            'score' => 86,
            'status' => 'PENDIENTE_COORDINACION',
            'created_by' => $admin->id,
            'owner_id' => $admin->id,
        ]);

        LaunchResearchSource::query()->firstOrCreate([
            'launch_proposal_id' => $launch->id,
            'source_type' => 'WhatsApp',
        ], [
            'interested_count' => 68,
            'preferred_day' => 'Sabado',
            'preferred_time' => '10:00',
            'notes' => 'Interes recurrente detectado por ventas y comunidad.',
            'created_by' => $admin->id,
        ]);

        $event = AcademicEvent::query()->firstOrCreate(['code' => 'REBA-202609-001'], [
            'launch_proposal_id' => $launch->id,
            'name' => $launch->commercial_name,
            'short_name' => 'Gestion de Emergencias',
            'event_type_id' => $launch->event_type_id,
            'modality_id' => $launch->modality_id,
            'specialty_id' => $launch->specialty_id,
            'audience_segment_id' => $launch->audience_segment_id,
            'certification_entity_id' => $certification->id,
            'start_date' => '2026-09-05',
            'duration_months' => 6,
            'total_hours' => 24,
            'status' => 'TENTATIVO',
            'commercial_priority' => 'ALTA',
            'created_by' => $admin->id,
        ]);

        EventAcademicStructure::query()->firstOrCreate(['academic_event_id' => $event->id], [
            'modules_count' => 6,
            'classes_count' => 6,
            'classes_per_month' => 1,
            'frequency_type' => 'monthly',
            'class_duration_minutes' => 240,
            'has_workshops' => true,
            'has_virtual_workshops' => true,
            'notes' => 'Un modulo mensual mas taller aplicado.',
        ]);

        if ($event->sessions()->doesntExist()) {
            app(EventScheduleGenerator::class)->persist($event, new ScheduleGenerationOptions(
                startDate: CarbonImmutable::parse('2026-09-05'),
                allowedWeekdays: [6],
                startTime: '10:00',
                classDurationMinutes: 240,
                frequencyType: 'monthly',
                roomId: $room->id,
                zoomAccountId: $zoom->id,
                speakerId: $speaker->id,
            ));

            app(ConflictDetector::class)->detectForEvent($event);
            $event->update(['end_date' => $event->sessions()->max('date')]);
        }

        $this->call(ImportedCalendarEventSeeder::class);
    }
}
