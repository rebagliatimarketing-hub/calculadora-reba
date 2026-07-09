<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchedulingConflict extends Model
{
    protected $guarded = [];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function academicEvent()
    {
        return $this->belongsTo(AcademicEvent::class);
    }

    public function session()
    {
        return $this->belongsTo(EventSession::class, 'event_session_id');
    }

    public function conflictEvent()
    {
        return $this->belongsTo(AcademicEvent::class, 'conflict_event_id');
    }

    public function rule()
    {
        return $this->belongsTo(SchedulingRule::class);
    }
}
