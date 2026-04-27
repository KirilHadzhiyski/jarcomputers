<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingMarket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminPricingMarketController extends Controller
{
    public function index(): View
    {
        return view('admin.pricing.markets.index', [
            'markets' => PricingMarket::query()->orderBy('name')->paginate(12),
            'seo' => [
                'title' => 'Markets and VAT',
                'description' => 'Manage target markets, VAT rates, and exchange rates for pricing analysis.',
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.pricing.markets.create', [
            'seo' => [
                'title' => 'New market',
                'description' => 'Add a target market and tax settings for pricing analysis.',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $market = PricingMarket::query()->create($this->validated($request));

        return redirect()
            ->route('admin.pricing.markets.edit', $market)
            ->with('status', 'Market created.');
    }

    public function edit(PricingMarket $market): View
    {
        return view('admin.pricing.markets.edit', [
            'market' => $market,
            'seo' => [
                'title' => "Edit {$market->name}",
                'description' => 'Update VAT and exchange-rate assumptions for this market.',
            ],
        ]);
    }

    public function update(Request $request, PricingMarket $market): RedirectResponse
    {
        $market->update($this->validated($request, $market));

        return redirect()
            ->route('admin.pricing.markets.edit', $market)
            ->with('status', 'Market updated.');
    }

    public function destroy(PricingMarket $market): RedirectResponse
    {
        $market->delete();

        return redirect()
            ->route('admin.pricing.markets.index')
            ->with('status', 'Market deleted.');
    }

    private function validated(Request $request, ?PricingMarket $market = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:8', Rule::unique('pricing_markets', 'code')->ignore($market?->id)],
            'currency_code' => ['required', 'string', 'max:8'],
            'vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'exchange_rate_to_bgn' => ['required', 'numeric', 'gt:0'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['currency_code'] = strtoupper($validated['currency_code']);
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
