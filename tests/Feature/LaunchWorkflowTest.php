<?php

namespace Tests\Feature;

use App\Models\AudienceSegment;
use App\Models\CertificationEntity;
use App\Models\EventType;
use App\Models\LaunchProposal;
use App\Models\Modality;
use App\Models\Room;
use App\Models\Speaker;
use App\Models\Specialty;
use App\Models\User;
use App\Models\ZoomAccount;
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
        $this->actingAs($admin)->get('/dashboard')->assertOk()->assertSee('Dashboard de lanzamientos');
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
}
