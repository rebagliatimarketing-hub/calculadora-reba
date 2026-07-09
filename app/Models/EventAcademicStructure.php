<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventAcademicStructure extends Model
{
    protected $guarded = [];

    protected $casts = [
        'has_workshops' => 'boolean',
        'has_presential_workshops' => 'boolean',
        'has_virtual_workshops' => 'boolean',
        'has_internship' => 'boolean',
        'has_simulation' => 'boolean',
    ];
}
