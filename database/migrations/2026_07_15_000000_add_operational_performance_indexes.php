<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->indexes() as [$table, $columns, $name]) {
            if (! Schema::hasIndex($table, $name)) {
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->index($columns, $name));
            }
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->indexes()) as [$table, , $name]) {
            if (Schema::hasIndex($table, $name)) {
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropIndex($name));
            }
        }
    }

    private function indexes(): array
    {
        return [
            ['launch_cycles', ['year', 'month'], 'launch_cycles_year_month_index'],
            ['launch_proposals', ['launch_cycle_id', 'status'], 'launch_proposals_cycle_status_index'],
            ['launch_proposals', ['status', 'created_at'], 'launch_proposals_status_created_index'],
            ['academic_events', 'launch_proposal_id', 'academic_events_launch_proposal_index'],
            ['academic_events', ['modality_id', 'deleted_at'], 'academic_events_modality_deleted_index'],
            ['academic_events', ['specialty_id', 'deleted_at'], 'academic_events_specialty_deleted_index'],
            ['event_sessions', ['date', 'start_time', 'end_time'], 'event_sessions_date_time_index'],
            ['event_sessions', ['academic_event_id', 'date'], 'event_sessions_event_date_index'],
            ['event_sessions', ['speaker_id', 'date'], 'event_sessions_speaker_date_index'],
            ['scheduling_conflicts', ['status', 'severity', 'created_at'], 'conflicts_status_severity_created_index'],
            ['scheduling_conflicts', ['academic_event_id', 'status'], 'conflicts_event_status_index'],
            ['scheduling_conflicts', 'event_session_id', 'conflicts_session_index'],
            ['approval_requests', ['status', 'created_at'], 'approval_requests_status_created_index'],
            ['approval_logs', ['approval_request_id', 'created_at'], 'approval_logs_request_created_index'],
        ];
    }
};
