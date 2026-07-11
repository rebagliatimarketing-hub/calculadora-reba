<?php

namespace App\Modules\Launches\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicEvent;
use App\Models\ApprovalWorkflow;
use App\Models\AudienceSegment;
use App\Models\CampaignHandoff;
use App\Models\CertificationEntity;
use App\Models\EventAcademicStructure;
use App\Models\EventType;
use App\Models\LaunchCycle;
use App\Models\LaunchProposal;
use App\Models\LaunchResearchSource;
use App\Models\Modality;
use App\Models\Room;
use App\Models\Speaker;
use App\Models\Specialty;
use App\Models\ZoomAccount;
use App\Modules\AcademicEvents\Services\EventScheduleGenerator;
use App\Modules\Approvals\Services\ApprovalWorkflowService;
use App\Modules\Launches\Services\LaunchScoringService;
use App\Modules\Notifications\Services\FormalEmailComposer;
use App\Modules\Scheduling\Services\ConflictDetector;
use App\Shared\DTOs\ScheduleGenerationOptions;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaunchController extends Controller
{
    public function index(Request $request)
    {
        $launches = LaunchProposal::query()
            ->with(['cycle', 'specialty', 'audience', 'eventType', 'modality', 'academicEvent.conflicts'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('month'), fn ($query) => $query->whereHas('cycle', fn ($cycle) => $cycle->where('month', $request->month)))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('launches.index', [
            'launches' => $launches,
            'statuses' => ['BORRADOR', 'PENDIENTE_COORDINACION', 'OBSERVADO_COORDINACION', 'APROBADO_FINAL', 'CONFLICTO_DETECTADO'],
        ]);
    }

    public function create()
    {
        return view('launches.create', $this->catalogs());
    }

    public function store(
        Request $request,
        LaunchScoringService $scoringService,
        EventScheduleGenerator $generator,
        ConflictDetector $conflictDetector,
    ) {
        $data = $request->validate([
            'tentative_name' => ['required', 'string', 'max:255'],
            'commercial_name' => ['required', 'string', 'max:255'],
            'specialty_id' => ['required', 'exists:specialties,id'],
            'audience_segment_id' => ['required', 'exists:audience_segments,id'],
            'event_type_id' => ['required', 'exists:event_types,id'],
            'modality_id' => ['required', 'exists:modalities,id'],
            'certification_entity_id' => ['nullable', 'exists:certification_entities,id'],
            'priority' => ['required', 'in:BAJA,MEDIA,ALTA,CRITICA'],
            'target_description' => ['nullable', 'string'],
            'commercial_justification' => ['nullable', 'string'],
            'academic_justification' => ['nullable', 'string'],
            'source_type' => ['required', 'string', 'max:80'],
            'interested_count' => ['nullable', 'integer', 'min:0'],
            'market_demand' => ['required', 'integer', 'min:0', 'max:100'],
            'survey_interest' => ['required', 'integer', 'min:0', 'max:100'],
            'specialty_recurrence' => ['required', 'integer', 'min:0', 'max:100'],
            'commercial_opportunity' => ['required', 'integer', 'min:0', 'max:100'],
            'operational_ease' => ['required', 'integer', 'min:0', 'max:100'],
            'differentiation' => ['required', 'integer', 'min:0', 'max:100'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:12'],
            'modules_count' => ['required', 'integer', 'min:1', 'max:24'],
            'classes_count' => ['required', 'integer', 'min:1', 'max:48'],
            'classes_per_month' => ['required', 'integer', 'min:1', 'max:8'],
            'frequency_type' => ['required', 'in:weekly,biweekly,monthly'],
            'class_duration_minutes' => ['required', 'integer', 'min:60', 'max:480'],
            'has_workshops' => ['nullable', 'boolean'],
            'has_presential_workshops' => ['nullable', 'boolean'],
            'has_virtual_workshops' => ['nullable', 'boolean'],
            'start_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'allowed_weekdays' => ['required', 'array', 'min:1'],
            'allowed_weekdays.*' => ['integer', 'between:1,7'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'zoom_account_id' => ['nullable', 'exists:zoom_accounts,id'],
            'speaker_id' => ['nullable', 'exists:speakers,id'],
        ]);

        $launch = DB::transaction(function () use ($data, $request, $scoringService, $generator, $conflictDetector): LaunchProposal {
            $cycle = LaunchCycle::query()->firstOrCreate([
                'year' => now()->year,
                'month' => now()->month,
            ], [
                'name' => 'Ciclo '.now()->translatedFormat('F Y'),
                'status' => 'ABIERTO',
                'opened_by' => $request->user()->id,
            ]);

            $code = 'REBA-'.now()->format('Ym').'-'.str_pad((string) (LaunchProposal::query()->count() + 1), 3, '0', STR_PAD_LEFT);
            $score = $scoringService->calculate($data);

            $launch = LaunchProposal::query()->create([
                'launch_cycle_id' => $cycle->id,
                'code' => $code,
                'tentative_name' => $data['tentative_name'],
                'commercial_name' => $data['commercial_name'],
                'final_name' => $data['commercial_name'],
                'specialty_id' => $data['specialty_id'],
                'audience_segment_id' => $data['audience_segment_id'],
                'event_type_id' => $data['event_type_id'],
                'modality_id' => $data['modality_id'],
                'certification_entity_id' => $data['certification_entity_id'] ?? null,
                'target_description' => $data['target_description'] ?? null,
                'commercial_justification' => $data['commercial_justification'] ?? null,
                'academic_justification' => $data['academic_justification'] ?? null,
                'duration_months' => $data['duration_months'],
                'classes_per_month' => $data['classes_per_month'],
                'priority' => $data['priority'],
                'score' => $score,
                'status' => 'EN_CALENDARIZACION',
                'created_by' => $request->user()->id,
                'owner_id' => $request->user()->id,
            ]);

            LaunchResearchSource::query()->create([
                'launch_proposal_id' => $launch->id,
                'source_type' => $data['source_type'],
                'interested_count' => $data['interested_count'] ?? 0,
                'notes' => $data['commercial_justification'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            $event = AcademicEvent::query()->create([
                'launch_proposal_id' => $launch->id,
                'code' => $code,
                'name' => $data['commercial_name'],
                'short_name' => str($data['commercial_name'])->limit(38, '')->toString(),
                'event_type_id' => $data['event_type_id'],
                'modality_id' => $data['modality_id'],
                'specialty_id' => $data['specialty_id'],
                'audience_segment_id' => $data['audience_segment_id'],
                'certification_entity_id' => $data['certification_entity_id'] ?? null,
                'start_date' => $data['start_date'],
                'duration_months' => $data['duration_months'],
                'total_hours' => (int) round(($data['classes_count'] * $data['class_duration_minutes']) / 60),
                'status' => 'TENTATIVO',
                'commercial_priority' => $data['priority'],
                'created_by' => $request->user()->id,
            ]);

            EventAcademicStructure::query()->create([
                'academic_event_id' => $event->id,
                'modules_count' => $data['modules_count'],
                'classes_count' => $data['classes_count'],
                'classes_per_month' => $data['classes_per_month'],
                'frequency_type' => $data['frequency_type'],
                'class_duration_minutes' => $data['class_duration_minutes'],
                'has_workshops' => (bool) ($data['has_workshops'] ?? false),
                'has_presential_workshops' => (bool) ($data['has_presential_workshops'] ?? false),
                'has_virtual_workshops' => (bool) ($data['has_virtual_workshops'] ?? false),
                'notes' => $data['academic_justification'] ?? null,
            ]);

            $generator->persist($event, ScheduleGenerationOptions::fromArray($data));
            $conflicts = $conflictDetector->detectForEvent($event);

            $event->update([
                'end_date' => $event->sessions()->max('date'),
                'status' => $conflicts->isNotEmpty() ? 'CONFLICTO_DETECTADO' : 'TENTATIVO',
            ]);

            $launch->update(['status' => $conflicts->isNotEmpty() ? 'CONFLICTO_DETECTADO' : 'PENDIENTE_COORDINACION']);

            CampaignHandoff::query()->create([
                'academic_event_id' => $event->id,
                'suggested_ads_start_date' => CarbonImmutable::parse($data['start_date'])->subWeeks(4)->toDateString(),
                'utm_campaign' => str($code.' '.$data['commercial_name'])->slug('_')->toString(),
                'created_by' => $request->user()->id,
            ]);

            return $launch;
        });

        return redirect()->route('launches.show', $launch)->with('status', 'Lanzamiento creado, calendarizado y validado.');
    }

    public function show(LaunchProposal $launch, FormalEmailComposer $emailComposer)
    {
        $launch->load([
            'cycle',
            'specialty',
            'audience',
            'eventType',
            'modality',
            'certification',
            'researchSources',
            'academicEvent.structure',
            'academicEvent.sessions.room',
            'academicEvent.sessions.zoomAccount',
            'academicEvent.sessions.speaker',
            'academicEvent.conflicts.session',
            'approvalRequests.logs',
        ]);

        return view('launches.show', [
            'launch' => $launch,
            'emailPreview' => $emailComposer->composeLaunchApproval($launch),
            'hasBlockingConflicts' => $this->hasBlockingConflicts($launch),
            ...$this->catalogs(),
        ]);
    }

    public function submitApproval(LaunchProposal $launch, ApprovalWorkflowService $service)
    {
        if ($this->hasBlockingConflicts($launch)) {
            return back()->withErrors('No se puede enviar a aprobacion mientras exista un cruce presencial abierto. Corrige la agenda y vuelve a calcular los conflictos.');
        }

        $workflow = ApprovalWorkflow::query()->where('module', 'launches')->where('is_active', true)->firstOrFail();
        $service->submitForApproval($launch, $workflow, auth()->user());

        return back()->with('status', 'La propuesta fue enviada a aprobacion.');
    }

    public function approve(LaunchProposal $launch, Request $request, ApprovalWorkflowService $service)
    {
        if ($this->hasBlockingConflicts($launch)) {
            return back()->withErrors('La aprobacion esta bloqueada por un cruce presencial abierto.');
        }

        $approvalRequest = $launch->approvalRequests()->latest()->first();

        if (! $approvalRequest) {
            return back()->withErrors('Primero envia la propuesta a aprobacion.');
        }

        $service->approve($approvalRequest, $request->user(), $request->input('comment'));

        return back()->with('status', 'La propuesta fue aprobada.');
    }

    public function reject(LaunchProposal $launch, Request $request, ApprovalWorkflowService $service)
    {
        $data = $request->validate(['comment' => ['required', 'string', 'max:1000']]);
        $approvalRequest = $launch->approvalRequests()->latest()->first();

        if (! $approvalRequest) {
            return back()->withErrors('Primero envia la propuesta a aprobacion.');
        }

        $service->reject($approvalRequest, $request->user(), $data['comment']);

        return back()->with('status', 'La propuesta fue observada.');
    }

    public function regenerateSessions(LaunchProposal $launch, Request $request, EventScheduleGenerator $generator, ConflictDetector $detector)
    {
        $event = $launch->academicEvent()->with('structure')->firstOrFail();
        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'frequency_type' => ['required', 'in:weekly,biweekly,monthly'],
            'class_duration_minutes' => ['required', 'integer', 'min:60', 'max:480'],
            'allowed_weekdays' => ['required', 'array', 'min:1'],
            'allowed_weekdays.*' => ['integer', 'between:1,7'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'zoom_account_id' => ['nullable', 'exists:zoom_accounts,id'],
            'speaker_id' => ['nullable', 'exists:speakers,id'],
        ]);

        $event->update(['start_date' => $data['start_date']]);
        $event->structure->update([
            'frequency_type' => $data['frequency_type'],
            'class_duration_minutes' => $data['class_duration_minutes'],
        ]);

        $generator->persist($event, ScheduleGenerationOptions::fromArray($data));
        $conflicts = $detector->detectForEvent($event);
        $event->update(['end_date' => $event->sessions()->max('date')]);
        $launch->update(['status' => $conflicts->isNotEmpty() ? 'CONFLICTO_DETECTADO' : 'PENDIENTE_COORDINACION']);

        return back()->with('status', 'Fechas regeneradas y conflictos recalculados.');
    }

    public function previewSchedule(AcademicEvent $event, Request $request, EventScheduleGenerator $generator)
    {
        return response()->json($generator->generate($event, ScheduleGenerationOptions::fromArray($request->all())));
    }

    public function previewConflicts(AcademicEvent $event, ConflictDetector $detector)
    {
        return response()->json($detector->detectForEvent($event));
    }

    private function hasBlockingConflicts(LaunchProposal $launch): bool
    {
        return $launch->academicEvent?->conflicts()
            ->where('status', 'ABIERTO')
            ->whereHas('rule', fn ($query) => $query->where('is_blocking', true))
            ->exists() ?? false;
    }

    private function catalogs(): array
    {
        return [
            'specialties' => Specialty::query()->where('is_active', true)->orderBy('name')->get(),
            'audiences' => AudienceSegment::query()->where('is_active', true)->orderBy('name')->get(),
            'eventTypes' => EventType::query()->orderBy('name')->get(),
            'modalities' => Modality::query()->orderBy('name')->get(),
            'certifications' => CertificationEntity::query()->where('is_active', true)->orderBy('name')->get(),
            'rooms' => Room::query()->where('is_active', true)->orderBy('name')->get(),
            'zoomAccounts' => ZoomAccount::query()->where('is_active', true)->orderBy('name')->get(),
            'speakers' => Speaker::query()->where('is_active', true)->orderBy('full_name')->get(),
        ];
    }
}
