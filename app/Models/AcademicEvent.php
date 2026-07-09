<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicEvent extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'formalized_at' => 'datetime',
    ];

    public function launchProposal()
    {
        return $this->belongsTo(LaunchProposal::class);
    }

    public function eventType()
    {
        return $this->belongsTo(EventType::class);
    }

    public function modality()
    {
        return $this->belongsTo(Modality::class);
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function audience()
    {
        return $this->belongsTo(AudienceSegment::class, 'audience_segment_id');
    }

    public function structure()
    {
        return $this->hasOne(EventAcademicStructure::class);
    }

    public function sessions()
    {
        return $this->hasMany(EventSession::class);
    }

    public function conflicts()
    {
        return $this->hasMany(SchedulingConflict::class);
    }
}
