<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportedCalendarEvent extends Model
{
    protected $guarded = [];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function academicEvent()
    {
        return $this->belongsTo(AcademicEvent::class);
    }

    public function session()
    {
        return $this->belongsTo(EventSession::class, 'event_session_id');
    }
}
