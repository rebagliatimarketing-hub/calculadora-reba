<?php

namespace App\Modules\Approvals\Services;

use App\Models\ApprovalLog;
use App\Models\ApprovalRequest;
use App\Models\ApprovalWorkflow;
use App\Models\LaunchProposal;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ApprovalWorkflowService
{
    public function submitForApproval(Model $approvable, ApprovalWorkflow $workflow, User $user): ApprovalRequest
    {
        $request = ApprovalRequest::query()->create([
            'workflow_id' => $workflow->id,
            'approvable_type' => $approvable::class,
            'approvable_id' => $approvable->id,
            'status' => 'PENDIENTE',
            'requested_by' => $user->id,
            'requested_at' => now(),
        ]);

        ApprovalLog::query()->create([
            'approval_request_id' => $request->id,
            'user_id' => $user->id,
            'action' => 'ENVIADO',
            'comment' => 'Propuesta enviada a validacion formal.',
        ]);

        if ($approvable instanceof LaunchProposal) {
            $approvable->update(['status' => 'PENDIENTE_COORDINACION']);
        }

        return $request;
    }

    public function approve(ApprovalRequest $request, User $user, ?string $comment = null): void
    {
        $request->update([
            'status' => 'APROBADO',
            'completed_at' => now(),
        ]);

        ApprovalLog::query()->create([
            'approval_request_id' => $request->id,
            'user_id' => $user->id,
            'action' => 'APROBADO',
            'comment' => $comment,
        ]);

        if ($request->approvable instanceof LaunchProposal) {
            $request->approvable->update(['status' => 'APROBADO_FINAL']);
            $request->approvable->academicEvent?->update(['status' => 'APROBADO_FINAL', 'approved_at' => now()]);
        }
    }

    public function reject(ApprovalRequest $request, User $user, string $comment): void
    {
        $request->update([
            'status' => 'OBSERVADO',
            'completed_at' => now(),
        ]);

        ApprovalLog::query()->create([
            'approval_request_id' => $request->id,
            'user_id' => $user->id,
            'action' => 'OBSERVADO',
            'comment' => $comment,
        ]);

        if ($request->approvable instanceof LaunchProposal) {
            $request->approvable->update(['status' => 'OBSERVADO_COORDINACION']);
        }
    }
}
