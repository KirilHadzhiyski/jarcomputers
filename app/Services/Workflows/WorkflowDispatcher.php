<?php

namespace App\Services\Workflows;

use App\Jobs\ExecuteWorkflow;
use App\Models\Workflow;
use App\Models\WorkflowRun;

class WorkflowDispatcher
{
    public function enqueue(Workflow $workflow, ?int $actorId = null, string $triggerType = 'manual'): ?WorkflowRun
    {
        if ($workflow->status !== 'active') {
            throw new WorkflowExecutionException('Само активен процес може да бъде стартиран.');
        }

        $duplicate = $workflow->runs()->whereIn('status', ['queued', 'running'])->exists();
        if ($duplicate) {
            return null;
        }

        $run = $workflow->runs()->create([
            'triggered_by' => $actorId,
            'trigger_type' => $triggerType,
            'status' => 'queued',
        ]);

        if ($triggerType === 'schedule') {
            $workflow->update(['next_run_at' => now()->addMinutes((int) $workflow->frequency_minutes)]);
        }

        ExecuteWorkflow::dispatch($run->id)->onConnection('integrations')->onQueue('integrations');

        return $run;
    }
}
