-- Indices para navegacion, calendario, conflictos y aprobaciones.
-- Es seguro ejecutar este archivo mas de una vez en Supabase.

create index if not exists launch_cycles_year_month_index
    on launch_cycles (year, month);

create index if not exists launch_proposals_cycle_status_index
    on launch_proposals (launch_cycle_id, status);

create index if not exists launch_proposals_status_created_index
    on launch_proposals (status, created_at);

create index if not exists academic_events_launch_proposal_index
    on academic_events (launch_proposal_id);

create index if not exists academic_events_modality_deleted_index
    on academic_events (modality_id, deleted_at);

create index if not exists academic_events_specialty_deleted_index
    on academic_events (specialty_id, deleted_at);

create index if not exists event_sessions_date_time_index
    on event_sessions (date, start_time, end_time);

create index if not exists event_sessions_event_date_index
    on event_sessions (academic_event_id, date);

create index if not exists event_sessions_speaker_date_index
    on event_sessions (speaker_id, date);

create index if not exists conflicts_status_severity_created_index
    on scheduling_conflicts (status, severity, created_at);

create index if not exists conflicts_event_status_index
    on scheduling_conflicts (academic_event_id, status);

create index if not exists conflicts_session_index
    on scheduling_conflicts (event_session_id);

create index if not exists approval_requests_status_created_index
    on approval_requests (status, created_at);

create index if not exists approval_logs_request_created_index
    on approval_logs (approval_request_id, created_at);
