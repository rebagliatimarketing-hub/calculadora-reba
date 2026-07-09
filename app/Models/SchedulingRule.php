<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchedulingRule extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_blocking' => 'boolean',
        'is_active' => 'boolean',
        'config_json' => 'array',
    ];
}
