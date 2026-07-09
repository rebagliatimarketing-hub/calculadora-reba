<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaunchProposal extends Model
{
    protected $guarded = [];

    public function cycle()
    {
        return $this->belongsTo(LaunchCycle::class, 'launch_cycle_id');
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function audience()
    {
        return $this->belongsTo(AudienceSegment::class, 'audience_segment_id');
    }

    public function eventType()
    {
        return $this->belongsTo(EventType::class);
    }

    public function modality()
    {
        return $this->belongsTo(Modality::class);
    }

    public function certification()
    {
        return $this->belongsTo(CertificationEntity::class, 'certification_entity_id');
    }

    public function academicEvent()
    {
        return $this->hasOne(AcademicEvent::class);
    }

    public function researchSources()
    {
        return $this->hasMany(LaunchResearchSource::class);
    }

    public function approvalRequests()
    {
        return $this->morphMany(ApprovalRequest::class, 'approvable');
    }
}
