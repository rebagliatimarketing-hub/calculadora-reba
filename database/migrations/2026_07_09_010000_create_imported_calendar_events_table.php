<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imported_calendar_events', function (Blueprint $table): void {
            $table->id();
            $table->string('import_code')->unique();
            $table->string('source_file');
            $table->string('source_sheet');
            $table->unsignedInteger('source_row');
            $table->unsignedInteger('source_col');
            $table->date('event_date');
            $table->text('raw_text');
            $table->string('parsed_title');
            $table->string('modality_slug');
            $table->string('event_type_slug');
            $table->string('specialty_slug');
            $table->string('audience_name');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('email')->nullable();
            $table->foreignId('academic_event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('event_session_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['event_date', 'source_file']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imported_calendar_events');
    }
};
