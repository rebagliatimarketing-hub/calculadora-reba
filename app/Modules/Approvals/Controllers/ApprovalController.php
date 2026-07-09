<?php

namespace App\Modules\Approvals\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;

class ApprovalController extends Controller
{
    public function index()
    {
        $requests = ApprovalRequest::query()
            ->with(['workflow', 'logs', 'approvable'])
            ->latest()
            ->paginate(15);

        return view('approvals.index', compact('requests'));
    }
}
