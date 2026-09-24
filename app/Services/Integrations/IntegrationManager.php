<?php

namespace App\Services\Integrations;

use App\Jobs\SyncIntegration;
use App\Models\IntegrationConnection;
use App\Models\IntegrationRun;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class IntegrationManager
{
    public function enqueue(IntegrationConnection $connection): ?IntegrationRun
    {
        if (IntegrationRegistry::missing($connection) !== []) {
            throw new IntegrationException('Попълнете липсващите настройки преди синхронизация.');
        }
        $lock = Cache::lock('integrations:enqueue:'.$connection->id, 10);
        if (! $lock->get()) {
            return null;
        }
        try {
            return DB::transaction(function () use ($connection) {
                $connection->runs()->where(function ($query) {
                    $query->where(fn ($q) => $q->where('status', 'queued')->where('created_at', '<', now()->subHours(2)))
                        ->orWhere(fn ($q) => $q->where('status', 'running')->where('started_at', '<', now()->subMinutes(20)));
                })->update([
                    'status' => 'failed', 'finished_at' => now(), 'message' => 'Задачата не завърши навреме. Проверете работния процес.',
                ]);
                if ($connection->runs()->whereIn('status', ['queued', 'running'])->exists()) {
                    return null;
                }
                $run = $connection->runs()->create(['status' => 'queued']);
                $connection->update(['next_run_at' => now()->addMinutes(IntegrationRegistry::definition($connection->provider)['interval'])]);
                SyncIntegration::dispatch($run->id)->onConnection('integrations')->onQueue('integrations')->afterCommit();

                return $run;
            });
        } finally {
            $lock->release();
        }
    }

    public function sync(IntegrationConnection $connection): int
    {
        if (IntegrationRegistry::missing($connection) !== []) {
            throw new IntegrationException('Настройките на интеграцията са непълни.');
        }

        return match ($connection->provider) {
            'skroutz' => app(SkroutzConnector::class)->sync($connection),
            'mailbox' => app(MailboxConnector::class)->sync($connection),
            'jar' => app(JarConnector::class)->sync($connection),
            'ecb' => app(EcbConnector::class)->sync($connection),
            'price_feed' => app(PriceFeedConnector::class)->sync($connection),
            'google_reviews' => app(GoogleReviewsConnector::class)->sync($connection),
            default => throw new IntegrationException('Неподдържана интеграция.'),
        };
    }
}
