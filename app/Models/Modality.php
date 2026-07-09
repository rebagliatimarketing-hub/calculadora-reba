<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modality extends Model
{
    protected $guarded = [];

    protected $casts = [
        'requires_room' => 'boolean',
        'requires_zoom' => 'boolean',
        'is_async' => 'boolean',
    ];
}
