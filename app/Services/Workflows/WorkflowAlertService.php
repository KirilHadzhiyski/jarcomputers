<?php

namespace App\Services\Workflows;

use App\Models\Workflow;
use App\Models\WorkflowAlert;
use App\Models\WorkflowRun;

class WorkflowAlertService
{
    public function create(Workflow $workflow, ?WorkflowRun $run, string $type, string $title, string $message, string $severity = 'warning', array $context = []): WorkflowAlert
    {
        return WorkflowAlert::query()->create([
            'workflow_id' => $workflow->id,
            'workflow_run_id' => $run?->id,
            'user_id' => $workflow->client_user_id,
            'type' => $type,
            'severity' => $severity,
            'status' => 'open',
            'title' => $title,
            'message' => $message,
            'context' => $context,
            'occurred_at' => now(),
        ]);
    }

    public function resolvePreviousFailures(Workflow $workflow): void
    {
        $workflow->alerts()->where('type', 'execution_failed')->whereIn('status', ['open', 'acknowledged'])->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }
}
