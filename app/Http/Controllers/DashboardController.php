<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tickets = $user->tickets()->latest()->take(5)->get();
        $ticketStats = $user->tickets()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');
        $subscription = $user->currentSubscription()->with('plan')->first();
        $usageCostByCurrency = $user->usageRecords()
            ->where('occurred_at', '>=', now()->startOfMonth())
            ->selectRaw('currency_code, sum(cost) as total_cost')
            ->groupBy('currency_code')
            ->pluck('total_cost', 'currency_code');
        $recordsUsedThisMonth = (int) $user->usageRecords()
            ->where('service_type', 'workflow')
            ->where('unit', 'record')
            ->where('occurred_at', '>=', now()->startOfMonth())
            ->sum('quantity');

        return view('dashboard.index', [
            'tickets' => $tickets,
            'ticketStats' => $ticketStats,
            'subscription' => $subscription,
            'usageCostByCurrency' => $usageCostByCurrency,
            'recordsUsedThisMonth' => $recordsUsedThisMonth,
            'workflowCount' => $user->workflows()->where('status', '!=', 'archived')->count(),
            'contactChannels' => User::CONTACT_CHANNEL_LABELS,
            'statusLabels' => Ticket::STATUS_LABELS,
            'seo' => [
                'title' => 'Моят профил',
                'description' => 'Профил, настройки за контакт и следене на поръчките.',
            ],
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:40'],
            'preferred_contact_channel' => ['required', 'in:email,phone'],
        ]);

        $request->user()->update($validated);

        return redirect()
            ->route('dashboard')
            ->with('status', 'Профилът и настройките за контакт са обновени.');
    }
}
