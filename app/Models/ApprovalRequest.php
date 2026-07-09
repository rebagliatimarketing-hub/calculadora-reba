<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    protected $guarded = [];

    protected $casts = [
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function approvable()
    {
        return $this->morphTo();
    }

    public function workflow()
    {
        return $this->belongsTo(ApprovalWorkflow::class);
    }

    public function logs()
    {
        return $this->hasMany(ApprovalLog::class);
    }
}
