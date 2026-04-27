<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Business\AdminBusinessSummaryService;
use App\Services\Pricing\PricingOpportunitySummaryService;
use App\Support\LaunchReadiness;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(
        private readonly PricingOpportunitySummaryService $pricingSummaryService,
        private readonly AdminBusinessSummaryService $businessSummaryService,
    ) {
    }

    public function index(): View
    {
        return view('admin.dashboard', [
            'ticketCount' => Ticket::query()->count(),
            'openTicketCount' => Ticket::query()->where('status', 'open')->count(),
            'readyTicketCount' => Ticket::query()->where('status', 'ready_for_pickup')->count(),
            'userCount' => User::query()->count(),
            'pricingSummary' => $this->pricingSummaryService->summary(),
            'businessSummary' => $this->businessSummaryService->summary(),
            'launchReadiness' => LaunchReadiness::report(),
            'latestTickets' => Ticket::query()->with('user')->latest()->take(8)->get(),
            'statusLabels' => Ticket::STATUS_LABELS,
            'seo' => [
                'title' => 'Admin панел',
                'description' => 'Административно управление на потребители, поръчки, известия и pricing intelligence.',
            ],
        ]);
    }
}
