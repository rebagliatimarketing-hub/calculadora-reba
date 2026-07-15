<?php

namespace Tests\Feature;

use App\Models\AcademicEvent;
use App\Models\AudienceSegment;
use App\Models\CertificationEntity;
use App\Models\EventSession;
use App\Models\EventType;
use App\Models\LaunchProposal;
use App\Models\Modality;
use App\Models\Room;
use App\Models\SchedulingConflict;
use App\Models\SchedulingRule;
use App\Models\Speaker;
use App\Models\Specialty;
use App\Models\User;
use App\Models\ZoomAccount;
use App\Modules\Scheduling\Services\ConflictDetector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaunchWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_login_and_loads_for_admin(): void
    {
        $this->seed();

        $this->get('/dashboard')->assertRedirect('/login');

        $admin = User::query()->firstOrFail();
        $response = $this->actingAs($admin)->get('/dashboard');

        $response
            ->assertOk()
            ->assertSee('Dashboard de lanzamientos')
            ->assertSee('data-sidebar-toggle', false)
            ->assertSee('aria-controls="app-sidebar"', false)
            ->assertSee('aria-current="page"', false);

        $this->assertSame(1, substr_count($response->getContent(), 'data-sidebar-toggle'));
    }

    public function test_operational_modules_and_launch_detail_load_for_admin(): void
    {
        $this->seed();

        $admin = User::query()->firstOrFail();
        $launch = LaunchProposal::query()->firstOrFail();

        foreach (['/launches', '/calendar', '/conflicts', '/approvals', '/reports/monthly'] as $path) {
            $this->actingAs($admin)->get($path)->assertOk();
        }

        $this->actingAs($admin)
            ->get(route('launches.show', $launch))
            ->assertOk()
            ->assertSee($launch->commercial_name)
            ->assertSee('Editar agenda');

        $this->actingAs($admin)
            ->get('/api/calendar/events?start=2026-07-01&end=2026-07-31')
            ->assertOk()
            ->assertJsonStructure([['id', 'title', 'date', 'start_time', 'end_time', 'modality', 'resource']]);
    }

    public function test_marketing_can_create_launch_generate_sessions_and_detect_conflicts(): void
    {
        $this->seed();

        $admin = User::query()->firstOrFail();

        $response = $this->actingAs($admin)->post(route('launches.store'), [
            'tentative_name' => 'Curso de Triaje Hospitalario',
            'commercial_name' => 'Curso de Triaje Hospitalario para Enfermeria',
            'specialty_id' => Specialty::query()->firstWhere('slug', 'enfermeria')->id,
            'audience_segment_id' => AudienceSegment::query()->firstWhere('name', 'Profesionales de salud')->id,
            'event_type_id' => EventType::query()->firstWhere('slug', 'curso')->id,
            'modality_id' => Modality::query()->firstWhere('slug', 'virtual')->id,
            'certification_entity_id' => CertificationEntity::query()->first()->id,
            'priority' => 'ALTA',
            'target_description' => 'Profesionales de salud.',
            'commercial_justification' => 'Demanda validada en WhatsApp.',
            'academic_justification' => 'Tema alineado a enfermeria.',
            'source_type' => 'WhatsApp',
            'interested_count' => 42,
            'market_demand' => 85,
            'survey_interest' => 75,
            'specialty_recurrence' => 80,
            'commercial_opportunity' => 90,
            'operational_ease' => 70,
            'differentiation' => 60,
            'duration_months' => 1,
            'modules_count' => 2,
            'classes_count' => 2,
            'classes_per_month' => 2,
            'frequency_type' => 'weekly',
            'class_duration_minutes' => 180,
            'has_workshops' => 0,
            'has_presential_workshops' => 0,
            'has_virtual_workshops' => 0,
            'start_date' => '2026-09-05',
            'start_time' => '10:00',
            'allowed_weekdays' => [6],
            'room_id' => Room::query()->first()->id,
            'zoom_account_id' => ZoomAccount::query()->first()->id,
            'speaker_id' => Speaker::query()->first()->id,
        ]);

        $launch = LaunchProposal::query()->where('commercial_name', 'Curso de Triaje Hospitalario para Enfermeria')->firstOrFail();

        $response->assertRedirect(route('launches.show', $launch));
        $this->assertNotNull($launch->academicEvent);
        $this->assertGreaterThan(0, $launch->academicEvent->sessions()->count());
        $this->assertGreaterThan(0, $launch->academicEvent->conflicts()->count());
    }

    public function test_virtual_sessions_can_share_a_schedule_and_zoom_account(): void
    {
        $this->seed();

        $source = AcademicEvent::query()->where('code', 'REBA-202609-001')->firstOrFail();
        $session = $source->sessions()->firstOrFail();
        $event = $this->duplicateEvent($source, 'REBA-VIRTUAL-TEST', $source->modality_id);

        $event->sessions()->create([
            ...$this->sessionData($session),
            'academic_event_id' => $event->id,
            'speaker_id' => null,
        ]);

        $conflicts = app(ConflictDetector::class)->detectForEvent($event);

        $this->assertCount(0, $conflicts);
    }

    public function test_overlapping_presential_sessions_create_a_blocking_conflict(): void
    {
        $this->seed();

        $source = AcademicEvent::query()->where('code', 'REBA-202609-001')->firstOrFail();
        $session = $source->sessions()->firstOrFail();
        $presential = Modality::query()->firstWhere('slug', 'presencial');
        $room = Room::query()->firstOrFail();

        foreach (['REBA-PRESENCIAL-A', 'REBA-PRESENCIAL-B'] as $code) {
            $event = $this->duplicateEvent($source, $code, $presential->id);
            $event->sessions()->create([
                ...$this->sessionData($session),
                'academic_event_id' => $event->id,
                'modality_id' => $presential->id,
                'room_id' => $room->id,
                'zoom_account_id' => null,
                'speaker_id' => null,
            ]);
        }

        $event = AcademicEvent::query()->where('code', 'REBA-PRESENCIAL-B')->firstOrFail();
        $conflicts = app(ConflictDetector::class)->detectForEvent($event);

        $this->assertTrue($conflicts->contains(fn ($conflict) => $conflict->rule?->code === 'ROOM_OVERLAP'));
        $this->assertTrue($conflicts->contains(fn ($conflict) => $conflict->severity === 'CRITICO'));
    }

    public function test_blocking_conflict_prevents_sending_launch_to_approval(): void
    {
        $this->seed();

        $admin = User::query()->firstOrFail();
        $launch = LaunchProposal::query()->where('code', 'REBA-202609-001')->firstOrFail();
        $session = $launch->academicEvent->sessions()->firstOrFail();

        SchedulingConflict::query()->create([
            'academic_event_id' => $launch->academicEvent->id,
            'event_session_id' => $session->id,
            'rule_id' => SchedulingRule::query()->firstWhere('code', 'ROOM_OVERLAP')->id,
            'severity' => 'CRITICO',
            'message' => 'Cruce presencial de prueba.',
            'status' => 'ABIERTO',
        ]);

        $response = $this->actingAs($admin)->post(route('launches.submit-approval', $launch));

        $response->assertSessionHasErrors();
        $this->assertSame(0, $launch->approvalRequests()->count());
    }

    public function test_resolve_conflict_button_closes_the_alert(): void
    {
        $this->seed();

        $admin = User::query()->firstOrFail();
        $launch = LaunchProposal::query()->where('code', 'REBA-202609-001')->firstOrFail();
        $session = $launch->academicEvent->sessions()->firstOrFail();
        $conflict = SchedulingConflict::query()->create([
            'academic_event_id' => $launch->academicEvent->id,
            'event_session_id' => $session->id,
            'rule_id' => SchedulingRule::query()->firstWhere('code', 'ROOM_OVERLAP')->id,
            'severity' => 'CRITICO',
            'message' => 'Cruce presencial de prueba.',
            'status' => 'ABIERTO',
        ]);

        $response = $this->actingAs($admin)->post(route('conflicts.resolve', $conflict), [
            'resolution_notes' => 'Agenda corregida y validada.',
        ]);

        $response->assertSessionHas('status');
        $this->assertSame('RESUELTO', $conflict->fresh()->status);
        $this->assertSame($admin->id, $conflict->fresh()->resolved_by);
        $this->assertSame('PENDIENTE_COORDINACION', $launch->fresh()->status);
    }

    private function duplicateEvent(AcademicEvent $source, string $code, int $modalityId): AcademicEvent
    {
        $event = $source->replicate();
        $event->launch_proposal_id = null;
        $event->code = $code;
        $event->name = $code;
        $event->short_name = $code;
        $event->modality_id = $modalityId;
        $event->save();

        return $event;
    }

    private function sessionData(EventSession $session): array
    {
        return $session->only([
            'session_number',
            'module_number',
            'session_type',
            'title',
            'date',
            'start_time',
            'end_time',
            'duration_minutes',
            'modality_id',
            'room_id',
            'zoom_account_id',
            'speaker_id',
            'status',
            'is_holiday',
            'is_exception',
        ]);
    }
}
