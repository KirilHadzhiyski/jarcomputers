<?php

use App\Models\IntegrationConnection;
use App\Models\PricingConfiguration;
use App\Models\PricingMarket;
use App\Models\RepairRequest;
use App\Models\Ticket;
use App\Services\Business\BusinessIntakeService;
use App\Services\Integrations\IntegrationManager;
use App\Services\Integrations\IntegrationRegistry;
use App\Services\Pricing\PricingAnalysisService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('integrations:dispatch {--provider= : Queue one configured provider now}', function (IntegrationManager $manager) {
    Cache::put('integrations:scheduler_heartbeat', now()->toIso8601String(), now()->addDay());
    $query = IntegrationConnection::where('enabled', true);
    if ($this->option('provider')) {
        $query->where('provider', $this->option('provider'));
    } else {
        $query->where(fn ($query) => $query->whereNull('next_run_at')->orWhere('next_run_at', '<=', now()));
    }
    foreach ($query->get() as $connection) {
        if (IntegrationRegistry::missing($connection) !== []) {
            continue;
        }
        $run = $manager->enqueue($connection);
        $this->info($connection->provider.($run ? ': queued #'.$run->id : ': already queued/running'));
    }
})->purpose('Queue enabled data integrations that are due');

Artisan::command('business:reconcile', function (BusinessIntakeService $intake) {
    RepairRequest::chunkById(100, function ($requests) use ($intake) {
        foreach ($requests as $request) {
            $intake->repairRequest($request);
        }
    });
    Ticket::with('user')->chunkById(100, function ($tickets) use ($intake) {
        foreach ($tickets as $ticket) {
            $intake->ticket($ticket);
        }
    });
    $this->info('Repair requests and tickets are linked to business records.');
})->purpose('Idempotently link existing website requests and tickets');

Schedule::command('integrations:dispatch')->everyMinute()->withoutOverlapping(5);

Artisan::command('pricing:refresh', function (PricingAnalysisService $analysis) {
    $markets = PricingMarket::where('is_active', true)->get();
    PricingConfiguration::where('status', '!=', 'archived')->chunkById(100, function ($configurations) use ($markets, $analysis) {
        foreach ($configurations as $configuration) {
            foreach ($markets as $market) {
                $analysis->analyze($configuration, $market);
            }
        }
    });
    $this->info('Pricing analyses refreshed using current rates and fresh offers.');
})->purpose('Refresh stored analyses and expire stale benchmark influence');

Schedule::command('pricing:refresh')->hourly()->withoutOverlapping(30);

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
