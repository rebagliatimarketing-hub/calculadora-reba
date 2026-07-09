<?php

namespace Database\Seeders;

use App\Models\AcademicEvent;
use App\Models\AudienceSegment;
use App\Models\CertificationEntity;
use App\Models\EventSession;
use App\Models\EventType;
use App\Models\ImportedCalendarEvent;
use App\Models\Modality;
use App\Models\Specialty;
use App\Models\User;
use App\Models\ZoomAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportedCalendarEventSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/calendar_events_july_2026_onward.json');

        if (! file_exists($path)) {
            return;
        }

        $records = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        $admin = User::query()->orderBy('id')->firstOrFail();
        $certification = CertificationEntity::query()->first();

        DB::transaction(function () use ($records, $admin, $certification): void {
            foreach ($records as $record) {
                $specialty = Specialty::query()->firstOrCreate([
                    'slug' => $record['specialty_slug'],
                ], [
                    'name' => $record['specialty_name'],
                    'is_active' => true,
                ]);

                $audience = AudienceSegment::query()->firstOrCreate([
                    'name' => $record['audience_name'],
                ], [
                    'description' => 'Importado desde calendario Excel.',
                    'career_area' => 'Salud',
                    'priority_level' => 3,
                    'is_active' => true,
                ]);

                $eventType = EventType::query()->firstWhere('slug', $record['event_type_slug'])
                    ?? EventType::query()->firstWhere('slug', 'curso');

                $modality = Modality::query()->firstWhere('slug', $record['modality_slug'])
                    ?? Modality::query()->firstWhere('slug', 'virtual');

                $zoomAccount = null;

                if (! empty($record['email'])) {
                    $zoomAccount = ZoomAccount::query()->firstOrCreate([
                        'email' => $record['email'],
                    ], [
                        'name' => 'Importado '.$record['email'],
                        'capacity' => 500,
                        'is_active' => true,
                    ]);
                }

                $start = Carbon::parse($record['event_date'].' '.$record['start_time']);
                $end = Carbon::parse($record['event_date'].' '.$record['end_time']);
                $durationMinutes = max(60, $start->diffInMinutes($end, false));

                $event = AcademicEvent::query()->updateOrCreate([
                    'code' => $record['import_code'],
                ], [
                    'name' => $record['title'],
                    'short_name' => Str::limit($record['title'], 60, ''),
                    'event_type_id' => $eventType->id,
                    'modality_id' => $modality->id,
                    'specialty_id' => $specialty->id,
                    'audience_segment_id' => $audience->id,
                    'certification_entity_id' => $certification?->id,
                    'start_date' => $record['event_date'],
                    'end_date' => $record['event_date'],
                    'duration_months' => 1,
                    'total_hours' => max(1, (int) ceil($durationMinutes / 60)),
                    'status' => 'IMPORTADO',
                    'commercial_priority' => 'MEDIA',
                    'created_by' => $admin->id,
                ]);

                $session = EventSession::query()->updateOrCreate([
                    'academic_event_id' => $event->id,
                    'session_number' => 1,
                ], [
                    'module_number' => 1,
                    'session_type' => 'IMPORTADO',
                    'title' => $record['title'],
                    'date' => $record['event_date'],
                    'start_time' => $record['start_time'],
                    'end_time' => $record['end_time'],
                    'duration_minutes' => $durationMinutes,
                    'modality_id' => $modality->id,
                    'zoom_account_id' => $zoomAccount?->id,
                    'status' => 'IMPORTADA',
                    'is_holiday' => false,
                    'is_exception' => false,
                ]);

                ImportedCalendarEvent::query()->updateOrCreate([
                    'import_code' => $record['import_code'],
                ], [
                    'source_file' => $record['source_file'],
                    'source_sheet' => $record['source_sheet'],
                    'source_row' => $record['source_row'],
                    'source_col' => $record['source_col'],
                    'event_date' => $record['event_date'],
                    'raw_text' => $record['raw_text'],
                    'parsed_title' => $record['title'],
                    'modality_slug' => $record['modality_slug'],
                    'event_type_slug' => $record['event_type_slug'],
                    'specialty_slug' => $record['specialty_slug'],
                    'audience_name' => $record['audience_name'],
                    'start_time' => $record['start_time'],
                    'end_time' => $record['end_time'],
                    'email' => $record['email'],
                    'academic_event_id' => $event->id,
                    'event_session_id' => $session->id,
                ]);
            }
        });
    }
}
