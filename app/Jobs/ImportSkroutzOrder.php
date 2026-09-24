<?php

namespace App\Jobs;

use App\Models\IntegrationConnection;
use App\Services\Integrations\IntegrationException;
use App\Services\Integrations\SkroutzConnector;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ImportSkroutzOrder implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 90;

    public int $uniqueFor = 600;

    public function __construct(public string $code)
    {
        $this->onConnection('integrations')->onQueue('integrations');
    }

    public function uniqueId(): string
    {
        return $this->code;
    }

    public function backoff(): array
    {
        return [30, 120];
    }

    public function handle(SkroutzConnector $connector): void
    {
        $connection = IntegrationConnection::where('provider', 'skroutz')->firstOrFail();
        if (! $connection->enabled || empty($connection->credentials['token'])) {
            return;
        }
        $run = $connection->runs()->create(['status' => 'running', 'started_at' => now()]);
        try {
            $connector->order($connection, $this->code);
            $run->update(['status' => 'succeeded', 'record_count' => 1, 'finished_at' => now()]);
            $connection->update(['last_success_at' => now()]);
        } catch (\Throwable $error) {
            $message = $error instanceof IntegrationException ? $error->getMessage() : 'Неуспешна връзка със Skroutz. Проверете достъпа и повторете webhook от Merchants панела.';
            $run->update(['status' => 'failed', 'finished_at' => now(), 'message' => $message]);
            throw new IntegrationException($message);
        }
    }
}
