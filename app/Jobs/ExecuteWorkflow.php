<?php

namespace App\Jobs;

use App\Models\WorkflowRun;
use App\Services\Workflows\WorkflowAlertService;
use App\Services\Workflows\WorkflowExecutionException;
use App\Services\Workflows\WorkflowQuotaException;
use App\Services\Workflows\WorkflowRunner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExecuteWorkflow implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;

    public int $tries = 3;

    public bool $failOnTimeout = true;

    public function __construct(public readonly int $runId) {}

    public function backoff(): array
    {
        return [60, 300];
    }

    public function handle(WorkflowRunner $runner, WorkflowAlertService $alerts): void
    {
        $run = WorkflowRun::query()->with('workflow')->find($this->runId);
        if (! $run || ! in_array($run->status, ['queued', 'running'], true)) {
            return;
        }

        $lock = Cache::lock('workflows:execute:'.$run->workflow_id, 660);
        if (! $lock->get()) {
            $run->update(['status' => 'cancelled', 'finished_at' => now(), 'summary' => 'Друг екземпляр на процеса вече се изпълнява.']);

            return;
        }

        try {
            $run->update([
                'status' => 'running',
                'attempt' => max(1, $this->attempts()),
                'started_at' => $run->started_at ?? now(),
                'error_message' => null,
            ]);
            $runner->execute($run->fresh());
        } catch (WorkflowQuotaException $exception) {
            $run->update([
                'status' => 'cancelled',
                'error_count' => 1,
                'error_message' => $exception->getMessage(),
                'summary' => 'Изпълнението е спряно от лимитите на абонаментния план.',
                'finished_at' => now(),
            ]);
            $alerts->create($run->workflow, $run, 'quota_exceeded', 'Достигнат е лимит на плана', $exception->getMessage(), 'warning');
        } catch (WorkflowExecutionException $exception) {
            $this->markFailed($run, $alerts, $exception->getMessage());
        } catch (Throwable $exception) {
            $this->markFailed($run, $alerts, 'Процесът не завърши. Проверете източника, worker-а и приложния лог.');
            Log::warning('Workflow execution failed', [
                'workflow_id' => $run->workflow_id,
                'run_id' => $run->id,
                'exception_type' => $exception::class,
            ]);
        } finally {
            $lock->release();
        }
    }

    public function failed(?Throwable $exception): void
    {
        WorkflowRun::query()->whereKey($this->runId)->whereIn('status', ['queued', 'running'])->update([
            'status' => 'failed',
            'error_count' => 1,
            'error_message' => 'Queue worker-ът прекъсна изпълнението. Проверете worker-а и времевия лимит.',
            'finished_at' => now(),
        ]);
    }

    private function markFailed(WorkflowRun $run, WorkflowAlertService $alerts, string $message): void
    {
        $run->update([
            'status' => 'failed',
            'error_count' => 1,
            'error_message' => $message,
            'summary' => 'Изпълнението завърши с грешка и не извърши външна промяна.',
            'finished_at' => now(),
        ]);
        $alerts->create($run->workflow, $run, 'execution_failed', 'Неуспешен работен процес', $message, 'critical');
    }
}
