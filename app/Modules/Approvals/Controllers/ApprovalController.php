<?php

namespace App\Modules\Approvals\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Models\LaunchProposal;

class ApprovalController extends Controller
{
    public function index()
    {
        $requests = ApprovalRequest::query()
            ->join('approval_workflows', 'approval_workflows.id', '=', 'approval_requests.workflow_id')
            ->leftJoin('launch_proposals', function ($join): void {
                $join->on('launch_proposals.id', '=', 'approval_requests.approvable_id')
                    ->where('approval_requests.approvable_type', LaunchProposal::class);
            })
            ->with('logs')
            ->select([
                'approval_requests.*',
                'approval_workflows.name as workflow_name',
                'launch_proposals.commercial_name as approvable_name',
            ])
            ->latest('approval_requests.created_at')
            ->paginate(15);

        return view('approvals.index', compact('requests'));
    }
}
