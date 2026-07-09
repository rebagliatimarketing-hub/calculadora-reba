-- Esquema base para Supabase/PostgreSQL.
-- Ejecutar este archivo ANTES de 002_seed_calendar_events_july_2026_onward.sql.

begin;

create table if not exists departments (
    id bigserial primary key,
    name varchar(255) not null,
    slug varchar(255) unique not null,
    created_at timestamp,
    updated_at timestamp
);

create table if not exists roles (
    id bigserial primary key,
    name varchar(255) not null,
    slug varchar(255) unique not null,
    description text,
    created_at timestamp,
    updated_at timestamp
);

create table if not exists users (
    id bigserial primary key,
    name varchar(255) not null,
    email varchar(255) unique not null,
    email_verified_at timestamp,
    password varchar(255) not null,
    remember_token varchar(100),
    department_id bigint references departments(id) on delete set null,
    role_id bigint references roles(id) on delete set null,
    is_active boolean not null default true,
    last_login_at timestamp,
    created_at timestamp,
    updated_at timestamp
);

create table if not exists password_reset_tokens (
    email varchar(255) primary key,
    token varchar(255) not null,
    created_at timestamp
);

create table if not exists sessions (
    id varchar(255) primary key,
    user_id bigint,
    ip_address varchar(45),
    user_agent text,
    payload text not null,
    last_activity integer not null
);

create index if not exists sessions_user_id_index on sessions(user_id);
create index if not exists sessions_last_activity_index on sessions(last_activity);

create table if not exists cache (
    key varchar(255) primary key,
    value text not null,
    expiration integer not null
);

create table if not exists cache_locks (
    key varchar(255) primary key,
    owner varchar(255) not null,
    expiration integer not null
);

create table if not exists jobs (
    id bigserial primary key,
    queue varchar(255) not null,
    payload text not null,
    attempts smallint not null,
    reserved_at integer,
    available_at integer not null,
    created_at integer not null
);

create index if not exists jobs_queue_index on jobs(queue);

create table if not exists job_batches (
    id varchar(255) primary key,
    name varchar(255) not null,
    total_jobs integer not null,
    pending_jobs integer not null,
    failed_jobs integer not null,
    failed_job_ids text not null,
    options text,
    cancelled_at integer,
    created_at integer not null,
    finished_at integer
);

create table if not exists failed_jobs (
    id bigserial primary key,
    uuid varchar(255) unique not null,
    connection text not null,
    queue text not null,
    payload text not null,
    exception text not null,
    failed_at timestamp not null default now()
);

create table if not exists specialties (
    id bigserial primary key,
    name varchar(255) not null,
    slug varchar(255) unique not null,
    parent_id bigint references specialties(id) on delete set null,
    is_active boolean not null default true,
    created_at timestamp,
    updated_at timestamp
);

create table if not exists audience_segments (
    id bigserial primary key,
    name varchar(255) not null,
    description text,
    career_area varchar(255),
    priority_level smallint not null default 3,
    is_active boolean not null default true,
    created_at timestamp,
    updated_at timestamp
);

create table if not exists event_types (
    id bigserial primary key,
    name varchar(255) not null,
    slug varchar(255) unique not null,
    requires_sessions boolean not null default true,
    requires_resource boolean not null default true,
    default_duration_months smallint not null default 1,
    created_at timestamp,
    updated_at timestamp
);

create table if not exists modalities (
    id bigserial primary key,
    name varchar(255) not null,
    slug varchar(255) unique not null,
    requires_room boolean not null default false,
    requires_zoom boolean not null default false,
    is_async boolean not null default false,
    created_at timestamp,
    updated_at timestamp
);

create table if not exists certification_entities (
    id bigserial primary key,
    name varchar(255) not null,
    type varchar(255) not null default 'Interna',
    description text,
    is_active boolean not null default true,
    created_at timestamp,
    updated_at timestamp
);

create table if not exists launch_cycles (
    id bigserial primary key,
    year smallint not null,
    month smallint not null,
    name varchar(255) not null,
    status varchar(255) not null default 'ABIERTO',
    opened_by bigint references users(id) on delete set null,
    closed_by bigint references users(id) on delete set null,
    closed_at timestamp,
    created_at timestamp,
    updated_at timestamp
);

create table if not exists launch_proposals (
    id bigserial primary key,
    launch_cycle_id bigint not null references launch_cycles(id) on delete cascade,
    code varchar(255) unique not null,
    tentative_name varchar(255) not null,
    commercial_name varchar(255),
    final_name varchar(255),
    specialty_id bigint not null references specialties(id),
    audience_segment_id bigint not null references audience_segments(id),
    event_type_id bigint not null references event_types(id),
    modality_id bigint not null references modalities(id),
    certification_entity_id bigint references certification_entities(id) on delete set null,
    target_description text,
    commercial_justification text,
    academic_justification text,
    duration_months smallint not null default 1,
    classes_per_month smallint not null default 1,
    priority varchar(255) not null default 'MEDIA',
    score smallint not null default 0,
    status varchar(255) not null default 'BORRADOR',
    created_by bigint not null references users(id),
    owner_id bigint references users(id) on delete set null,
    created_at timestamp,
    updated_at timestamp
);

create table if not exists launch_research_sources (
    id bigserial primary key,
    launch_proposal_id bigint not null references launch_proposals(id) on delete cascade,
    source_type varchar(255) not null,
    source_name varchar(255),
    research_date date,
    interested_count integer not null default 0,
    preferred_day varchar(255),
    preferred_time varchar(255),
    preferred_duration varchar(255),
    winning_topic varchar(255),
    alternative_topic varchar(255),
    evidence_url varchar(255),
    notes text,
    created_by bigint references users(id) on delete set null,
    created_at timestamp,
    updated_at timestamp
);

create table if not exists rooms (
    id bigserial primary key,
    name varchar(255) not null,
    location varchar(255),
    floor varchar(255),
    capacity smallint not null default 30,
    setup_time_minutes smallint not null default 30,
    cleanup_time_minutes smallint not null default 30,
    is_active boolean not null default true,
    created_at timestamp,
    updated_at timestamp
);

create table if not exists zoom_accounts (
    id bigserial primary key,
    name varchar(255) not null,
    email varchar(255) unique not null,
    capacity smallint not null default 500,
    is_active boolean not null default true,
    created_at timestamp,
    updated_at timestamp
);

create table if not exists speakers (
    id bigserial primary key,
    full_name varchar(255) not null,
    profession varchar(255),
    specialty varchar(255),
    phone varchar(255),
    email varchar(255),
    notes text,
    is_active boolean not null default true,
    created_at timestamp,
    updated_at timestamp
);

create table if not exists academic_events (
    id bigserial primary key,
    launch_proposal_id bigint references launch_proposals(id) on delete set null,
    code varchar(255) unique not null,
    name varchar(255) not null,
    short_name varchar(255),
    event_type_id bigint not null references event_types(id),
    modality_id bigint not null references modalities(id),
    specialty_id bigint not null references specialties(id),
    audience_segment_id bigint not null references audience_segments(id),
    certification_entity_id bigint references certification_entities(id) on delete set null,
    start_date date,
    end_date date,
    duration_months smallint not null default 1,
    total_hours smallint not null default 0,
    credits numeric(5, 2),
    status varchar(255) not null default 'TENTATIVO',
    commercial_priority varchar(255) not null default 'MEDIA',
    created_by bigint not null references users(id),
    approved_at timestamp,
    formalized_at timestamp,
    created_at timestamp,
    updated_at timestamp,
    deleted_at timestamp
);

create table if not exists event_academic_structures (
    id bigserial primary key,
    academic_event_id bigint not null references academic_events(id) on delete cascade,
    modules_count smallint not null default 1,
    classes_count smallint not null default 1,
    classes_per_month smallint not null default 1,
    frequency_type varchar(255) not null default 'monthly',
    class_duration_minutes smallint not null default 180,
    has_workshops boolean not null default false,
    has_presential_workshops boolean not null default false,
    has_virtual_workshops boolean not null default false,
    has_internship boolean not null default false,
    has_simulation boolean not null default false,
    notes text,
    created_at timestamp,
    updated_at timestamp
);

create table if not exists event_sessions (
    id bigserial primary key,
    academic_event_id bigint not null references academic_events(id) on delete cascade,
    session_number smallint not null,
    module_number smallint not null default 1,
    session_type varchar(255) not null default 'CLASE',
    title varchar(255) not null,
    date date not null,
    start_time time not null,
    end_time time not null,
    duration_minutes smallint not null,
    modality_id bigint not null references modalities(id),
    room_id bigint references rooms(id) on delete set null,
    zoom_account_id bigint references zoom_accounts(id) on delete set null,
    speaker_id bigint references speakers(id) on delete set null,
    status varchar(255) not null default 'TENTATIVA',
    is_holiday boolean not null default false,
    is_exception boolean not null default false,
    created_at timestamp,
    updated_at timestamp
);

create table if not exists scheduling_rules (
    id bigserial primary key,
    code varchar(255) unique not null,
    name varchar(255) not null,
    description text,
    severity varchar(255) not null default 'ADVERTENCIA',
    is_blocking boolean not null default false,
    is_active boolean not null default true,
    config_json jsonb,
    created_at timestamp,
    updated_at timestamp
);

create table if not exists scheduling_conflicts (
    id bigserial primary key,
    academic_event_id bigint not null references academic_events(id) on delete cascade,
    event_session_id bigint references event_sessions(id) on delete cascade,
    conflict_event_id bigint references academic_events(id) on delete set null,
    conflict_session_id bigint references event_sessions(id) on delete set null,
    rule_id bigint references scheduling_rules(id) on delete set null,
    severity varchar(255) not null default 'ADVERTENCIA',
    message text not null,
    recommendation text,
    status varchar(255) not null default 'ABIERTO',
    resolved_by bigint references users(id) on delete set null,
    resolved_at timestamp,
    resolution_notes text,
    created_at timestamp,
    updated_at timestamp
);

create table if not exists holidays (
    id bigserial primary key,
    date date unique not null,
    name varchar(255) not null,
    type varchar(255) not null default 'NACIONAL',
    is_recurring boolean not null default false,
    created_at timestamp,
    updated_at timestamp
);

create table if not exists approval_workflows (
    id bigserial primary key,
    name varchar(255) not null,
    module varchar(255) not null,
    is_active boolean not null default true,
    created_at timestamp,
    updated_at timestamp
);

create table if not exists approval_steps (
    id bigserial primary key,
    workflow_id bigint not null references approval_workflows(id) on delete cascade,
    step_order smallint not null,
    department_id bigint references departments(id) on delete set null,
    role_id bigint references roles(id) on delete set null,
    action_required varchar(255) not null,
    is_required boolean not null default true,
    created_at timestamp,
    updated_at timestamp
);

create table if not exists approval_requests (
    id bigserial primary key,
    workflow_id bigint not null references approval_workflows(id),
    approvable_type varchar(255) not null,
    approvable_id bigint not null,
    status varchar(255) not null default 'PENDIENTE',
    requested_by bigint not null references users(id),
    requested_at timestamp,
    completed_at timestamp,
    created_at timestamp,
    updated_at timestamp
);

create index if not exists approval_requests_approvable_index on approval_requests(approvable_type, approvable_id);

create table if not exists approval_logs (
    id bigserial primary key,
    approval_request_id bigint not null references approval_requests(id) on delete cascade,
    step_id bigint references approval_steps(id) on delete set null,
    user_id bigint not null references users(id),
    action varchar(255) not null,
    comment text,
    created_at timestamp,
    updated_at timestamp
);

create table if not exists audit_logs (
    id bigserial primary key,
    user_id bigint references users(id) on delete set null,
    auditable_type varchar(255) not null,
    auditable_id bigint not null,
    action varchar(255) not null,
    old_values_json jsonb,
    new_values_json jsonb,
    ip_address varchar(255),
    user_agent varchar(255),
    created_at timestamp not null default now()
);

create index if not exists audit_logs_auditable_index on audit_logs(auditable_type, auditable_id);

create table if not exists campaign_handoffs (
    id bigserial primary key,
    academic_event_id bigint not null references academic_events(id) on delete cascade,
    status varchar(255) not null default 'PENDIENTE_BRIEF',
    brief_status varchar(255) not null default 'PENDIENTE',
    copy_status varchar(255) not null default 'PENDIENTE',
    design_status varchar(255) not null default 'PENDIENTE',
    landing_status varchar(255) not null default 'PENDIENTE',
    ads_status varchar(255) not null default 'PENDIENTE',
    suggested_ads_start_date date,
    walink varchar(255),
    utm_campaign varchar(255),
    utm_adset varchar(255),
    utm_ad varchar(255),
    notes text,
    created_by bigint references users(id) on delete set null,
    created_at timestamp,
    updated_at timestamp
);

create table if not exists imported_calendar_events (
    id bigserial primary key,
    import_code varchar(40) unique not null,
    source_file varchar(120) not null,
    source_sheet varchar(80) not null,
    source_row integer not null,
    source_col integer not null,
    event_date date not null,
    raw_text text not null,
    parsed_title varchar(255) not null,
    modality_slug varchar(80) not null,
    event_type_slug varchar(80) not null,
    specialty_slug varchar(120) not null,
    audience_name varchar(160) not null,
    start_time time not null,
    end_time time not null,
    email varchar(255),
    academic_event_id bigint references academic_events(id) on delete set null,
    event_session_id bigint references event_sessions(id) on delete set null,
    created_at timestamp default now(),
    updated_at timestamp default now()
);

create index if not exists imported_calendar_events_date_source_index on imported_calendar_events(event_date, source_file);

insert into departments (name, slug, created_at, updated_at) values
    ('Marketing', 'marketing', now(), now()),
    ('Coordinacion Academica', 'coordinacion-academica', now(), now()),
    ('Academica', 'academica', now(), now()),
    ('Ventas', 'ventas', now(), now()),
    ('Gerencia', 'gerencia', now(), now()),
    ('Trafico Digital', 'trafico-digital', now(), now())
on conflict (slug) do update set name = excluded.name, updated_at = now();

insert into roles (name, slug, description, created_at, updated_at) values
    ('Super Admin', 'super-admin', 'Control total del sistema.', now(), now()),
    ('Jefatura de Marketing', 'jefatura-marketing', 'Crea y valida ciclos de lanzamiento.', now(), now()),
    ('Coordinacion Academica', 'coordinacion-academica', 'Valida estructura academica y fechas.', now(), now()),
    ('Gerencia', 'gerencia', 'Revisa aprobaciones finales.', now(), now())
on conflict (slug) do update set name = excluded.name, description = excluded.description, updated_at = now();

insert into users (name, email, password, department_id, role_id, is_active, created_at, updated_at)
select 'Admin REBA',
       'admin@rebagliati.edu.pe',
       '$2y$10$IRPSziUwcQxr00RUH4sCfuAXAwNMG6Hf6/Hkzw0UWJJ0JdeBHy/mi',
       d.id,
       r.id,
       true,
       now(),
       now()
from departments d
join roles r on r.slug = 'super-admin'
where d.slug = 'marketing'
on conflict (email) do update set is_active = true, department_id = excluded.department_id, role_id = excluded.role_id, updated_at = now();

insert into specialties (name, slug, is_active, created_at, updated_at) values
    ('Enfermeria', 'enfermeria', true, now(), now()),
    ('Tecnicos en Enfermeria', 'tecnicos-en-enfermeria', true, now(), now()),
    ('Obstetricia', 'obstetricia', true, now(), now()),
    ('Medicina', 'medicina', true, now(), now()),
    ('Fisioterapia', 'fisioterapia', true, now(), now()),
    ('Farmacia', 'farmacia', true, now(), now()),
    ('Laboratorio Clinico', 'laboratorio-clinico', true, now(), now()),
    ('Radiologia', 'radiologia', true, now(), now()),
    ('Psicologia', 'psicologia', true, now(), now()),
    ('Odontologia', 'odontologia', true, now(), now()),
    ('Sin clasificar', 'sin-clasificar', true, now(), now())
on conflict (slug) do update set name = excluded.name, is_active = true, updated_at = now();

insert into audience_segments (name, description, career_area, priority_level, is_active, created_at, updated_at)
select 'Profesionales de salud', 'Profesionales titulados o bachilleres', 'Salud', 5, true, now(), now()
where not exists (select 1 from audience_segments where name = 'Profesionales de salud');

insert into audience_segments (name, description, career_area, priority_level, is_active, created_at, updated_at)
select 'Tecnicos de enfermeria', 'Tecnicos que requieren talleres y fines de semana', 'Salud tecnica', 4, true, now(), now()
where not exists (select 1 from audience_segments where name = 'Tecnicos de enfermeria');

insert into audience_segments (name, description, career_area, priority_level, is_active, created_at, updated_at)
select 'Publico general', 'Personas interesadas en formacion corta', 'General', 2, true, now(), now()
where not exists (select 1 from audience_segments where name = 'Publico general');

insert into event_types (name, slug, default_duration_months, created_at, updated_at) values
    ('Diplomado', 'diplomado', 6, now(), now()),
    ('Diplomado intensivo', 'diplomado-intensivo', 3, now(), now()),
    ('Curso', 'curso', 1, now(), now()),
    ('Curso modular', 'curso-modular', 4, now(), now()),
    ('Taller', 'taller', 1, now(), now()),
    ('Webinar', 'webinar', 1, now(), now())
on conflict (slug) do update set name = excluded.name, default_duration_months = excluded.default_duration_months, updated_at = now();

insert into modalities (name, slug, requires_room, requires_zoom, is_async, created_at, updated_at) values
    ('Presencial', 'presencial', true, false, false, now(), now()),
    ('Virtual', 'virtual', false, true, false, now(), now()),
    ('Semipresencial', 'semipresencial', true, true, false, now(), now()),
    ('Hibrido', 'hibrido', true, true, false, now(), now()),
    ('Asincronico', 'asincronico', false, false, true, now(), now())
on conflict (slug) do update set name = excluded.name, requires_room = excluded.requires_room, requires_zoom = excluded.requires_zoom, is_async = excluded.is_async, updated_at = now();

insert into certification_entities (name, type, description, is_active, created_at, updated_at)
select 'Rebagliati Diplomados', 'Interna', 'Certificacion institucional para programas academicos.', true, now(), now()
where not exists (select 1 from certification_entities where name = 'Rebagliati Diplomados');

insert into rooms (name, location, floor, capacity, created_at, updated_at)
select 'Auditorio Principal', 'Lima', '1', 80, now(), now()
where not exists (select 1 from rooms where name = 'Auditorio Principal');

insert into zoom_accounts (name, email, capacity, is_active, created_at, updated_at) values
    ('Zoom Academico 1', 'zoom1@rebagliati.edu.pe', 500, true, now(), now())
on conflict (email) do update set name = excluded.name, is_active = true, updated_at = now();

insert into speakers (full_name, profession, specialty, email, is_active, created_at, updated_at)
select 'Dra. Maria Salazar', 'Enfermera especialista', 'Enfermeria', 'maria.salazar@example.com', true, now(), now()
where not exists (select 1 from speakers where full_name = 'Dra. Maria Salazar');

insert into scheduling_rules (code, name, description, severity, is_blocking, is_active, created_at, updated_at) values
    ('HOLIDAY_PRESENTIAL', 'No programar presencial en feriado', 'Regla base del motor de conflictos.', 'BLOQUEANTE', true, true, now(), now()),
    ('ROOM_OVERLAP', 'Cruce de aula', 'Regla base del motor de conflictos.', 'CRITICO', true, true, now(), now()),
    ('ZOOM_OVERLAP', 'Cruce de Zoom', 'Regla base del motor de conflictos.', 'CRITICO', true, true, now(), now()),
    ('SPEAKER_OVERLAP', 'Cruce de docente', 'Regla base del motor de conflictos.', 'ALTO', false, true, now(), now()),
    ('AUDIENCE_OVERLAP', 'Cruce de publico objetivo', 'Regla base del motor de conflictos.', 'ADVERTENCIA', false, true, now(), now()),
    ('WEAK_WEEKDAY', 'Dia de asistencia debil', 'Regla base del motor de conflictos.', 'ADVERTENCIA', false, true, now(), now())
on conflict (code) do update set name = excluded.name, severity = excluded.severity, is_blocking = excluded.is_blocking, is_active = true, updated_at = now();

insert into approval_workflows (name, module, is_active, created_at, updated_at)
select 'Aprobacion de lanzamientos', 'launches', true, now(), now()
where not exists (select 1 from approval_workflows where module = 'launches');

commit;
