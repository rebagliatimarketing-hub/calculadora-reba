<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignHandoff extends Model
{
    protected $guarded = [];

    protected $casts = [
        'suggested_ads_start_date' => 'date',
    ];
}
