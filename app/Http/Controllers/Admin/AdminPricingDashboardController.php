<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingAnalysisResult;
use App\Models\PricingBenchmark;
use App\Models\PricingConfiguration;
use App\Models\PricingMarket;
use App\Models\PricingSource;
use App\Services\Pricing\PricingOpportunitySummaryService;
use Illuminate\View\View;

class AdminPricingDashboardController extends Controller
{
    public function __construct(
        private readonly PricingOpportunitySummaryService $summaryService,
    ) {
    }

    public function index(): View
    {
        return view('admin.pricing.overview', [
            'summary' => $this->summaryService->summary(),
            'configurations' => PricingConfiguration::query()->orderBy('name')->get(),
            'markets' => PricingMarket::query()->orderBy('name')->get(),
            'sources' => PricingSource::query()->with('market')->orderBy('name')->get(),
            'latestBenchmarks' => PricingBenchmark::query()
                ->with(['configuration', 'market', 'source'])
                ->latest('collected_at')
                ->take(8)
                ->get(),
            'latestAnalyses' => PricingAnalysisResult::query()
                ->with(['configuration', 'market'])
                ->latest('calculated_at')
                ->take(8)
                ->get(),
            'seo' => [
                'title' => 'Admin pricing overview',
                'description' => 'Internal pricing intelligence dashboard for configurations, markets, sources, and viability analysis.',
            ],
        ]);
    }
}
