<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaunchCycle extends Model
{
    protected $guarded = [];

    public function proposals()
    {
        return $this->hasMany(LaunchProposal::class);
    }
}
