<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminSubscriptionPlanController extends Controller
{
    public function index(): View
    {
        return view('admin.subscription-plans.index', [
            'plans' => SubscriptionPlan::query()->withCount('subscriptions')->latest()->paginate(15),
            'seo' => ['title' => 'Абонаментни планове', 'description' => 'Управление на лимити, функции и месечни такси.'],
        ]);
    }

    public function create(): View
    {
        return view('admin.subscription-plans.create', ['seo' => ['title' => 'Нов абонаментен план']]);
    }

    public function store(Request $request): RedirectResponse
    {
        $plan = SubscriptionPlan::query()->create($this->validated($request));

        return redirect()->route('admin.subscription-plans.edit', $plan)->with('status', 'Абонаментният план е създаден.');
    }

    public function edit(SubscriptionPlan $subscriptionPlan): View
    {
        return view('admin.subscription-plans.edit', [
            'plan' => $subscriptionPlan,
            'seo' => ['title' => "Редакция: {$subscriptionPlan->name}"],
        ]);
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan): RedirectResponse
    {
        $subscriptionPlan->update($this->validated($request, $subscriptionPlan));

        return back()->with('status', 'Абонаментният план е обновен.');
    }

    public function destroy(SubscriptionPlan $subscriptionPlan): RedirectResponse
    {
        if ($subscriptionPlan->subscriptions()->exists()) {
            return back()->withErrors(['plan' => 'План с абонаментна история не може да бъде изтрит. Деактивирайте го.']);
        }

        $subscriptionPlan->delete();

        return redirect()->route('admin.subscription-plans.index')->with('status', 'Абонаментният план е изтрит.');
    }

    private function validated(Request $request, ?SubscriptionPlan $plan = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'alpha_dash:ascii', 'max:80', Rule::unique('subscription_plans', 'code')->ignore($plan?->id)],
            'description' => ['nullable', 'string', 'max:5000'],
            'monthly_price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'currency_code' => ['required', 'regex:/^[A-Z]{3}$/'],
            'workflow_limit' => ['required', 'integer', 'min:0', 'max:10000'],
            'records_per_month' => ['required', 'integer', 'min:0'],
            'ai_budget' => ['required', 'numeric', 'min:0', 'max:999999'],
            'features' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['features'] = collect(preg_split('/[\r\n]+/', $validated['features'] ?? ''))->map(fn ($value) => trim($value))->filter()->unique()->values()->all();
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
