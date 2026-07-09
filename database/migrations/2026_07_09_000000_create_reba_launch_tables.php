<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('module');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('department_id')->nullable()->after('password')->constrained()->nullOnDelete();
            $table->foreignId('role_id')->nullable()->after('department_id')->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('role_id');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });

        Schema::create('specialties', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('parent_id')->nullable()->constrained('specialties')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('audience_segments', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('career_area')->nullable();
            $table->unsignedTinyInteger('priority_level')->default(3);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('event_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('requires_sessions')->default(true);
            $table->boolean('requires_resource')->default(true);
            $table->unsignedTinyInteger('default_duration_months')->default(1);
            $table->timestamps();
        });

        Schema::create('modalities', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('requires_room')->default(false);
            $table->boolean('requires_zoom')->default(false);
            $table->boolean('is_async')->default(false);
            $table->timestamps();
        });

        Schema::create('certification_entities', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type')->default('Interna');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('launch_cycles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->string('name');
            $table->string('status')->default('ABIERTO');
            $table->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('launch_proposals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('launch_cycle_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('tentative_name');
            $table->string('commercial_name')->nullable();
            $table->string('final_name')->nullable();
            $table->foreignId('specialty_id')->constrained()->restrictOnDelete();
            $table->foreignId('audience_segment_id')->constrained()->restrictOnDelete();
            $table->foreignId('event_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('modality_id')->constrained()->restrictOnDelete();
            $table->foreignId('certification_entity_id')->nullable()->constrained()->nullOnDelete();
            $table->text('target_description')->nullable();
            $table->text('commercial_justification')->nullable();
            $table->text('academic_justification')->nullable();
            $table->unsignedTinyInteger('duration_months')->default(1);
            $table->unsignedTinyInteger('classes_per_month')->default(1);
            $table->string('priority')->default('MEDIA');
            $table->unsignedTinyInteger('score')->default(0);
            $table->string('status')->default('BORRADOR');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('launch_research_sources', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('launch_proposal_id')->constrained()->cascadeOnDelete();
            $table->string('source_type');
            $table->string('source_name')->nullable();
            $table->date('research_date')->nullable();
            $table->unsignedInteger('interested_count')->default(0);
            $table->string('preferred_day')->nullable();
            $table->string('preferred_time')->nullable();
            $table->string('preferred_duration')->nullable();
            $table->string('winning_topic')->nullable();
            $table->string('alternative_topic')->nullable();
            $table->string('evidence_url')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('rooms', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->string('floor')->nullable();
            $table->unsignedSmallInteger('capacity')->default(30);
            $table->unsignedSmallInteger('setup_time_minutes')->default(30);
            $table->unsignedSmallInteger('cleanup_time_minutes')->default(30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('zoom_accounts', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->unsignedSmallInteger('capacity')->default(500);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('speakers', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('profession')->nullable();
            $table->string('specialty')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('academic_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('launch_proposal_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->foreignId('event_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('modality_id')->constrained()->restrictOnDelete();
            $table->foreignId('specialty_id')->constrained()->restrictOnDelete();
            $table->foreignId('audience_segment_id')->constrained()->restrictOnDelete();
            $table->foreignId('certification_entity_id')->nullable()->constrained()->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedTinyInteger('duration_months')->default(1);
            $table->unsignedSmallInteger('total_hours')->default(0);
            $table->decimal('credits', 5, 2)->nullable();
            $table->string('status')->default('TENTATIVO');
            $table->string('commercial_priority')->default('MEDIA');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('formalized_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('event_academic_structures', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('academic_event_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('modules_count')->default(1);
            $table->unsignedTinyInteger('classes_count')->default(1);
            $table->unsignedTinyInteger('classes_per_month')->default(1);
            $table->string('frequency_type')->default('monthly');
            $table->unsignedSmallInteger('class_duration_minutes')->default(180);
            $table->boolean('has_workshops')->default(false);
            $table->boolean('has_presential_workshops')->default(false);
            $table->boolean('has_virtual_workshops')->default(false);
            $table->boolean('has_internship')->default(false);
            $table->boolean('has_simulation')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('event_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('academic_event_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('session_number');
            $table->unsignedSmallInteger('module_number')->default(1);
            $table->string('session_type')->default('CLASE');
            $table->string('title');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('duration_minutes');
            $table->foreignId('modality_id')->constrained()->restrictOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('zoom_account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('speaker_id')->nullable()->constrained('speakers')->nullOnDelete();
            $table->string('status')->default('TENTATIVA');
            $table->boolean('is_holiday')->default(false);
            $table->boolean('is_exception')->default(false);
            $table->timestamps();
        });

        Schema::create('scheduling_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('severity')->default('ADVERTENCIA');
            $table->boolean('is_blocking')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('config_json')->nullable();
            $table->timestamps();
        });

        Schema::create('scheduling_conflicts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('academic_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_session_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('conflict_event_id')->nullable()->constrained('academic_events')->nullOnDelete();
            $table->foreignId('conflict_session_id')->nullable()->constrained('event_sessions')->nullOnDelete();
            $table->foreignId('rule_id')->nullable()->constrained('scheduling_rules')->nullOnDelete();
            $table->string('severity')->default('ADVERTENCIA');
            $table->text('message');
            $table->text('recommendation')->nullable();
            $table->string('status')->default('ABIERTO');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('holidays', function (Blueprint $table): void {
            $table->id();
            $table->date('date')->unique();
            $table->string('name');
            $table->string('type')->default('NACIONAL');
            $table->boolean('is_recurring')->default(false);
            $table->timestamps();
        });

        Schema::create('approval_workflows', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('module');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('approval_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workflow_id')->constrained('approval_workflows')->cascadeOnDelete();
            $table->unsignedTinyInteger('step_order');
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('role_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action_required');
            $table->boolean('is_required')->default(true);
            $table->timestamps();
        });

        Schema::create('approval_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workflow_id')->constrained('approval_workflows')->restrictOnDelete();
            $table->string('approvable_type');
            $table->unsignedBigInteger('approvable_id');
            $table->string('status')->default('PENDIENTE');
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['approvable_type', 'approvable_id']);
        });

        Schema::create('approval_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('approval_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('step_id')->nullable()->constrained('approval_steps')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('action');
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            $table->string('action');
            $table->json('old_values_json')->nullable();
            $table->json('new_values_json')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['auditable_type', 'auditable_id']);
        });

        Schema::create('campaign_handoffs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('academic_event_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('PENDIENTE_BRIEF');
            $table->string('brief_status')->default('PENDIENTE');
            $table->string('copy_status')->default('PENDIENTE');
            $table->string('design_status')->default('PENDIENTE');
            $table->string('landing_status')->default('PENDIENTE');
            $table->string('ads_status')->default('PENDIENTE');
            $table->date('suggested_ads_start_date')->nullable();
            $table->string('walink')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_adset')->nullable();
            $table->string('utm_ad')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_handoffs');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('approval_logs');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('approval_steps');
        Schema::dropIfExists('approval_workflows');
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('scheduling_conflicts');
        Schema::dropIfExists('scheduling_rules');
        Schema::dropIfExists('event_sessions');
        Schema::dropIfExists('event_academic_structures');
        Schema::dropIfExists('academic_events');
        Schema::dropIfExists('speakers');
        Schema::dropIfExists('zoom_accounts');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('launch_research_sources');
        Schema::dropIfExists('launch_proposals');
        Schema::dropIfExists('launch_cycles');
        Schema::dropIfExists('certification_entities');
        Schema::dropIfExists('modalities');
        Schema::dropIfExists('event_types');
        Schema::dropIfExists('audience_segments');
        Schema::dropIfExists('specialties');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn(['is_active', 'last_login_at']);
        });

        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('departments');
    }
};
