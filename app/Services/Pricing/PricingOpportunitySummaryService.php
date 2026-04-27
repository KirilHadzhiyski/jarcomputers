<?php

namespace App\Services\Pricing;

use App\Models\PricingAnalysisResult;
use App\Models\PricingBenchmark;
use App\Models\PricingConfiguration;
use App\Models\PricingMarket;
use App\Models\PricingSource;

class PricingOpportunitySummaryService
{
    public function summary(): array
    {
        return [
            'configuration_count' => PricingConfiguration::query()->count(),
            'active_market_count' => PricingMarket::query()->where('is_active', true)->count(),
            'active_source_count' => PricingSource::query()->where('is_active', true)->count(),
            'recent_benchmark_count' => PricingBenchmark::query()->where('collected_at', '>=', now()->subDays(7))->count(),
            'viable_count' => PricingAnalysisResult::query()->where('viability_status', 'viable')->count(),
        ];
    }
}
