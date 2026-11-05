<?php

namespace App\Services\Subscriptions;

use App\Models\ClientSubscription;
use App\Models\SubscriptionPayment;
use App\Models\UsageRecord;

class SubscriptionSummaryService
{
    public function summary(): array
    {
        $current = ClientSubscription::query()
            ->with('plan')
            ->whereIn('status', ['trial', 'active', 'past_due', 'paused'])
            ->get();
        $mrrByCurrency = $current->where('status', 'active')
            ->groupBy(fn ($subscription) => $subscription->plan?->currency_code ?? 'EUR')
            ->map(fn ($subscriptions) => $subscriptions->sum(fn ($subscription) => (float) $subscription->plan?->monthly_price))
            ->all();
        $usageByCurrency = UsageRecord::query()
            ->where('occurred_at', '>=', now()->startOfMonth())
            ->selectRaw('currency_code, sum(cost) as total_cost')
            ->groupBy('currency_code')
            ->pluck('total_cost', 'currency_code')
            ->map(fn ($value) => (float) $value)
            ->all();

        return [
            'active_count' => $current->where('status', 'active')->count(),
            'trial_count' => $current->where('status', 'trial')->count(),
            'past_due_count' => $current->where('status', 'past_due')->count(),
            'monthly_recurring_revenue' => $current->where('status', 'active')->sum(fn ($subscription) => (float) $subscription->plan?->monthly_price),
            'mrr_by_currency' => $mrrByCurrency,
            'ai_cost_this_month' => (float) UsageRecord::query()
                ->where('service_type', 'ai')
                ->where('occurred_at', '>=', now()->startOfMonth())
                ->sum('cost'),
            'total_usage_cost_this_month' => (float) UsageRecord::query()
                ->where('occurred_at', '>=', now()->startOfMonth())
                ->sum('cost'),
            'usage_cost_by_currency' => $usageByCurrency,
            'failed_payments_this_month' => SubscriptionPayment::query()
                ->where('status', 'failed')
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
        ];
    }
}
