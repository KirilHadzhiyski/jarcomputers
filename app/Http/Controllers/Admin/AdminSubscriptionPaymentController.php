<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientSubscription;
use App\Models\SubscriptionPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminSubscriptionPaymentController extends Controller
{
    public function index(): View
    {
        return view('admin.subscription-payments.index', [
            'payments' => SubscriptionPayment::query()->with(['user', 'subscription.plan'])->latest()->paginate(20),
            'statusLabels' => SubscriptionPayment::STATUS_LABELS,
            'seo' => ['title' => 'Абонаментни плащания'],
        ]);
    }

    public function create(): View
    {
        return view('admin.subscription-payments.create', $this->formData(null));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $subscription = ClientSubscription::query()->findOrFail($validated['client_subscription_id']);
        $validated['user_id'] = $subscription->user_id;
        $payment = SubscriptionPayment::query()->create($validated);

        return redirect()->route('admin.subscription-payments.edit', $payment)->with('status', 'Плащането е записано.');
    }

    public function edit(SubscriptionPayment $subscriptionPayment): View
    {
        return view('admin.subscription-payments.edit', $this->formData($subscriptionPayment));
    }

    public function update(Request $request, SubscriptionPayment $subscriptionPayment): RedirectResponse
    {
        $validated = $this->validated($request, $subscriptionPayment);
        $subscription = ClientSubscription::query()->findOrFail($validated['client_subscription_id']);
        $validated['user_id'] = $subscription->user_id;
        $subscriptionPayment->update($validated);

        return back()->with('status', 'Плащането е обновено.');
    }

    public function destroy(SubscriptionPayment $subscriptionPayment): RedirectResponse
    {
        $subscriptionPayment->delete();

        return redirect()->route('admin.subscription-payments.index')->with('status', 'Плащането е изтрито.');
    }

    private function formData(?SubscriptionPayment $payment): array
    {
        return [
            'payment' => $payment,
            'subscriptions' => ClientSubscription::query()->with(['user', 'plan'])->latest()->get(),
            'statusLabels' => SubscriptionPayment::STATUS_LABELS,
            'seo' => ['title' => $payment ? 'Редакция на абонаментно плащане' : 'Ново абонаментно плащане'],
        ];
    }

    private function validated(Request $request, ?SubscriptionPayment $payment = null): array
    {
        return $request->validate([
            'client_subscription_id' => ['required', 'exists:client_subscriptions,id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999'],
            'currency_code' => ['required', 'regex:/^[A-Z]{3}$/'],
            'status' => ['required', Rule::in(array_keys(SubscriptionPayment::STATUS_LABELS))],
            'provider' => ['nullable', 'string', 'max:50'],
            'external_reference' => ['nullable', 'string', 'max:255', Rule::unique('subscription_payments', 'external_reference')->ignore($payment?->id)],
            'billed_at' => ['nullable', 'date'],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date', 'after_or_equal:period_start'],
        ]);
    }
}
