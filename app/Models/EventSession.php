<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventSession extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'is_holiday' => 'boolean',
        'is_exception' => 'boolean',
    ];

    public function academicEvent()
    {
        return $this->belongsTo(AcademicEvent::class);
    }

    public function modality()
    {
        return $this->belongsTo(Modality::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function zoomAccount()
    {
        return $this->belongsTo(ZoomAccount::class);
    }

    public function speaker()
    {
        return $this->belongsTo(Speaker::class);
    }
}
