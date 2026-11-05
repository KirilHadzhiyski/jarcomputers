<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientSubscription;
use App\Models\SubscriptionPayment;
use App\Models\UsageRecord;
use App\Services\Subscriptions\SubscriptionSummaryService;
use Illuminate\View\View;

class AdminSubscriptionDashboardController extends Controller
{
    public function __construct(private readonly SubscriptionSummaryService $summaryService) {}

    public function index(): View
    {
        return view('admin.subscriptions.dashboard', [
            'summary' => $this->summaryService->summary(),
            'subscriptions' => ClientSubscription::query()->with(['user', 'plan'])->latest()->take(10)->get(),
            'payments' => SubscriptionPayment::query()->with(['user', 'subscription.plan'])->latest()->take(10)->get(),
            'usageByProvider' => UsageRecord::query()
                ->where('occurred_at', '>=', now()->startOfMonth())
                ->selectRaw('provider, currency_code, sum(cost) as total_cost, sum(quantity) as total_quantity')
                ->groupBy('provider', 'currency_code')
                ->orderByDesc('total_cost')
                ->get(),
            'subscriptionStatusLabels' => ClientSubscription::STATUS_LABELS,
            'paymentStatusLabels' => SubscriptionPayment::STATUS_LABELS,
            'seo' => [
                'title' => 'Клиенти и абонаменти',
                'description' => 'Абонаментни планове, периодични плащания и разходи за външни услуги.',
            ],
        ]);
    }
}
