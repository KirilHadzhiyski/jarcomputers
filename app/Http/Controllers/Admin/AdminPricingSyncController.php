<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingConfiguration;
use App\Models\PricingMarket;
use App\Models\PricingSource;
use App\Services\Pricing\PricingBenchmarkIngestionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminPricingSyncController extends Controller
{
    public function store(Request $request, PricingBenchmarkIngestionService $ingestionService): RedirectResponse
    {
        $validated = $request->validate([
            'pricing_configuration_id' => ['required', 'exists:pricing_configurations,id'],
            'pricing_market_id' => ['required', 'exists:pricing_markets,id'],
            'pricing_source_id' => ['required', 'exists:pricing_sources,id'],
        ]);

        $configuration = PricingConfiguration::query()->findOrFail($validated['pricing_configuration_id']);
        $market = PricingMarket::query()->findOrFail($validated['pricing_market_id']);
        $source = PricingSource::query()->findOrFail($validated['pricing_source_id']);

        abort_if($source->pricing_market_id !== $market->id, 422, 'Selected source does not belong to the chosen market.');

        $baseNetMarket = round((float) $configuration->base_price_bgn / max((float) $market->exchange_rate_to_bgn, 0.0001), 2);
        $grossPrice = round(($baseNetMarket * $this->sourceFactor($source)) * (1 + ((float) $market->vat_rate / 100)), 2);

        $ingestionService->ingest([
            'pricing_configuration_id' => $configuration->id,
            'pricing_market_id' => $market->id,
            'pricing_source_id' => $source->id,
            'observed_price' => $grossPrice,
            'currency_code' => $market->currency_code,
            'price_includes_vat' => true,
            'availability_text' => 'Synced sample benchmark',
            'competitor_name' => $source->name,
            'product_title' => "{$configuration->name} - {$market->name} sync",
            'product_url' => rtrim((string) $source->base_url, '/').'/search?q='.urlencode($configuration->sku),
            'input_method' => 'scraper',
            'is_active' => true,
            'collected_at' => now(),
        ], $request->user());

        return redirect()
            ->route('admin.pricing.dashboard')
            ->with('status', 'Sample sync completed and benchmark stored as scraper data.');
    }

    private function sourceFactor(PricingSource $source): float
    {
        $sourceKey = strtolower($source->source_key);

        return match (true) {
            str_contains($sourceKey, 'bestprice') => 1.14,
            str_contains($sourceKey, 'skroutz') => 1.11,
            str_contains($sourceKey, 'emag') => 1.08,
            default => 1.1,
        };
    }
}
