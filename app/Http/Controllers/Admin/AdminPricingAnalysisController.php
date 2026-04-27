<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingAnalysisResult;
use App\Models\PricingBenchmark;
use App\Models\PricingConfiguration;
use App\Models\PricingMarket;
use App\Services\Pricing\PricingAnalysisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPricingAnalysisController extends Controller
{
    public function index(Request $request): View
    {
        $configurations = PricingConfiguration::query()->orderBy('name')->get();
        $markets = PricingMarket::query()->orderBy('name')->get();
        $selectedConfiguration = $configurations->firstWhere('id', (int) $request->query('configuration_id')) ?? $configurations->first();
        $selectedMarket = $markets->firstWhere('id', (int) $request->query('market_id')) ?? $markets->first();

        $analysis = null;
        $benchmarks = collect();

        if ($selectedConfiguration && $selectedMarket) {
            $analysis = PricingAnalysisResult::query()
                ->where('pricing_configuration_id', $selectedConfiguration->id)
                ->where('pricing_market_id', $selectedMarket->id)
                ->first();

            $benchmarks = PricingBenchmark::query()
                ->with('source')
                ->where('pricing_configuration_id', $selectedConfiguration->id)
                ->where('pricing_market_id', $selectedMarket->id)
                ->latest('collected_at')
                ->get();
        }

        return view('admin.pricing.analysis.index', [
            'configurations' => $configurations,
            'markets' => $markets,
            'selectedConfiguration' => $selectedConfiguration,
            'selectedMarket' => $selectedMarket,
            'analysis' => $analysis,
            'benchmarks' => $benchmarks,
            'statusLabels' => PricingAnalysisResult::STATUS_LABELS,
            'seo' => [
                'title' => 'Pricing analysis',
                'description' => 'Run VAT-aware pricing analysis against benchmark data per market.',
            ],
        ]);
    }

    public function store(Request $request, PricingAnalysisService $analysisService): RedirectResponse
    {
        $validated = $request->validate([
            'pricing_configuration_id' => ['required', 'exists:pricing_configurations,id'],
            'pricing_market_id' => ['required', 'exists:pricing_markets,id'],
        ]);

        $configuration = PricingConfiguration::query()->findOrFail($validated['pricing_configuration_id']);
        $market = PricingMarket::query()->findOrFail($validated['pricing_market_id']);
        $analysisService->analyze($configuration, $market);

        return redirect()
            ->route('admin.pricing.analysis.index', [
                'configuration_id' => $configuration->id,
                'market_id' => $market->id,
            ])
            ->with('status', 'Analysis recalculated.');
    }
}
