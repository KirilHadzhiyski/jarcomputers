<?php

namespace App\Jobs;

use App\Models\IntegrationRun;
use App\Services\Integrations\IntegrationException;
use App\Services\Integrations\IntegrationManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncIntegration implements ShouldQueue
{
    use Queueable;

    public int $timeout = 900;

    public int $tries = 1;

    public bool $failOnTimeout = true;

    public function __construct(public readonly int $runId) {}

    public function handle(IntegrationManager $manager): void
    {
        $run = IntegrationRun::with('connection')->find($this->runId);
        if (! $run || $run->status !== 'queued') {
            return;
        }
        $lock = Cache::lock('integrations:execute:'.$run->integration_connection_id, 1000);
        if (! $lock->get()) {
            $run->update(['status' => 'cancelled', 'finished_at' => now(), 'message' => 'Вече се изпълнява обновяване.']);

            return;
        }
        try {
            Cache::put('integrations:worker_heartbeat', now()->toIso8601String(), now()->addDay());
            $run->update(['status' => 'running', 'started_at' => now()]);
            $count = $manager->sync($run->connection);
            $run->connection->update(['last_success_at' => now()]);
            $run->update(['status' => 'succeeded', 'record_count' => $count, 'finished_at' => now(), 'message' => 'Обновяването завърши успешно.']);
        } catch (Throwable $exception) {
            $message = $exception instanceof IntegrationException ? $exception->getMessage() : 'Импортът не завърши. Проверете връзката, достъпа и формата на данните.';
            $run->update(['status' => 'failed', 'finished_at' => now(), 'message' => $message]);
            Log::warning('Integration failed', ['provider' => $run->connection->provider, 'run_id' => $run->id, 'exception_type' => $exception::class]);
        } finally {
            $lock->release();
        }
    }

    public function failed(?Throwable $exception): void
    {
        IntegrationRun::whereKey($this->runId)->whereIn('status', ['queued', 'running'])->update([
            'status' => 'failed', 'finished_at' => now(), 'message' => 'Работният процес прекъсна. Проверете worker-а и лимита за време.',
        ]);
    }
}
