<?php

namespace App\Services\Workflows;

use App\Models\UsageRecord;
use App\Models\Workflow;

class WorkflowQuotaService
{
    public function assertCanStart(Workflow $workflow): void
    {
        $client = $workflow->client;
        if (! $client) {
            return;
        }
        if ($client->isAccessBlocked()) {
            throw new WorkflowQuotaException('Клиентският профил е спрян или деактивиран.');
        }

        $subscription = $client->currentSubscription;
        if (! $subscription && $client->subscriptions()->exists()) {
            throw new WorkflowQuotaException('Клиентът няма активен или пробен абонамент.');
        }
        if ($subscription && ! in_array($subscription->status, ['trial', 'active'], true)) {
            throw new WorkflowQuotaException('Абонаментът на клиента е временно спрян или има просрочено плащане.');
        }

        $plan = $subscription?->plan;
        if (! $plan) {
            return;
        }

        if ($plan->records_per_month > 0 && $this->recordsUsed($workflow) >= $plan->records_per_month) {
            throw new WorkflowQuotaException('Месечният лимит за обработени записи е изчерпан.');
        }

        if ($plan->ai_budget > 0 && $this->aiCost($workflow, $plan->currency_code) >= (float) $plan->ai_budget) {
            throw new WorkflowQuotaException('Месечният AI бюджет е изчерпан.');
        }
    }

    public function assertRecordsFit(Workflow $workflow, int $incoming): void
    {
        $limit = (int) ($workflow->client?->currentSubscription?->plan?->records_per_month ?? 0);
        if ($limit > 0 && $this->recordsUsed($workflow) + $incoming > $limit) {
            throw new WorkflowQuotaException("Получените {$incoming} записа надвишават оставащия месечен лимит на клиента.");
        }
    }

    public function recordsUsed(Workflow $workflow): int
    {
        if (! $workflow->client_user_id) {
            return 0;
        }

        return (int) UsageRecord::query()
            ->where('user_id', $workflow->client_user_id)
            ->where('service_type', 'workflow')
            ->where('unit', 'record')
            ->where('occurred_at', '>=', now()->startOfMonth())
            ->sum('quantity');
    }

    private function aiCost(Workflow $workflow, string $currency): float
    {
        return (float) UsageRecord::query()
            ->where('user_id', $workflow->client_user_id)
            ->where('service_type', 'ai')
            ->where('currency_code', $currency)
            ->where('occurred_at', '>=', now()->startOfMonth())
            ->sum('cost');
    }
}
