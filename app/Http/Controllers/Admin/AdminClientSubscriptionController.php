<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminClientSubscriptionController extends Controller
{
    public function index(): View
    {
        return view('admin.client-subscriptions.index', [
            'subscriptions' => ClientSubscription::query()->with(['user', 'plan'])->latest()->paginate(20),
            'statusLabels' => ClientSubscription::STATUS_LABELS,
            'seo' => ['title' => 'Клиентски абонаменти'],
        ]);
    }

    public function create(): View
    {
        return view('admin.client-subscriptions.create', $this->formData(null));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $subscription = ClientSubscription::query()->create($validated);

        return redirect()->route('admin.client-subscriptions.edit', $subscription)->with('status', 'Клиентският абонамент е създаден.');
    }

    public function edit(ClientSubscription $clientSubscription): View
    {
        return view('admin.client-subscriptions.edit', $this->formData($clientSubscription));
    }

    public function update(Request $request, ClientSubscription $clientSubscription): RedirectResponse
    {
        $clientSubscription->update($this->validated($request, $clientSubscription));

        return back()->with('status', 'Абонаментът е обновен.');
    }

    public function destroy(ClientSubscription $clientSubscription): RedirectResponse
    {
        if ($clientSubscription->payments()->exists()) {
            return back()->withErrors(['subscription' => 'Абонамент с плащания не може да бъде изтрит. Променете статуса му на прекратен.']);
        }

        $clientSubscription->delete();

        return redirect()->route('admin.client-subscriptions.index')->with('status', 'Абонаментът е изтрит.');
    }

    private function formData(?ClientSubscription $subscription): array
    {
        return [
            'subscription' => $subscription,
            'users' => User::query()->where('role', 'user')->orderBy('name')->get(['id', 'name', 'email']),
            'plans' => SubscriptionPlan::query()->orderByDesc('is_active')->orderBy('name')->get(),
            'statusLabels' => ClientSubscription::STATUS_LABELS,
            'seo' => ['title' => $subscription ? 'Редакция на абонамент' : 'Нов клиентски абонамент'],
        ];
    }

    private function validated(Request $request, ?ClientSubscription $subscription = null): array
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'subscription_plan_id' => ['required', 'exists:subscription_plans,id'],
            'status' => ['required', Rule::in(array_keys(ClientSubscription::STATUS_LABELS))],
            'starts_at' => ['required', 'date'],
            'trial_ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'renews_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'external_provider' => ['nullable', 'string', 'max:50'],
            'external_customer_id' => ['nullable', 'string', 'max:255'],
            'external_subscription_id' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if (in_array($validated['status'], ['trial', 'active', 'past_due', 'paused'], true)) {
            $exists = ClientSubscription::query()
                ->where('user_id', $validated['user_id'])
                ->whereIn('status', ['trial', 'active', 'past_due', 'paused'])
                ->when($subscription, fn ($query) => $query->whereKeyNot($subscription->id))
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'user_id' => 'Клиентът вече има текущ абонамент.',
                ]);
            }
        }

        return $validated;
    }
}
