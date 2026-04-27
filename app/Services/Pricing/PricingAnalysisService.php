<?php

namespace App\Services\Pricing;

use App\Models\PricingAnalysisResult;
use App\Models\PricingBenchmark;
use App\Models\PricingConfiguration;
use App\Models\PricingMarket;

class PricingAnalysisService
{
    public function analyze(PricingConfiguration $configuration, PricingMarket $market): array
    {
        $benchmarks = PricingBenchmark::query()
            ->where('pricing_configuration_id', $configuration->id)
            ->where('pricing_market_id', $market->id)
            ->where('is_active', true)
            ->orderByDesc('collected_at')
            ->get();

        $count = $benchmarks->count();
        $grossPrices = $benchmarks->map(fn (PricingBenchmark $benchmark): float => (float) ($benchmark->price_including_vat ?? $benchmark->observed_price));

        if ($count === 0) {
            return $this->persistResult($configuration, $market, [
                'reference_benchmark_count' => 0,
                'avg_benchmark_price' => null,
                'min_benchmark_price' => null,
                'max_benchmark_price' => null,
                'base_price_market_currency' => round($configuration->base_price_bgn / max((float) $market->exchange_rate_to_bgn, 0.0001), 2),
                'suggested_price_excluding_vat' => null,
                'suggested_price_including_vat' => null,
                'suggested_price_bgn_equivalent' => null,
                'target_margin_amount' => null,
                'target_margin_percent' => null,
                'viability_status' => 'not_viable',
                'competition_note' => 'No active benchmark prices are available for this market.',
                'analysis_summary' => 'Add benchmark data before viability can be calculated.',
            ]);
        }

        $avg = round($grossPrices->avg(), 2);
        $min = round($grossPrices->min(), 2);
        $max = round($grossPrices->max(), 2);
        $suggestedGross = $avg;
        $suggestedNet = round($suggestedGross / (1 + ((float) $market->vat_rate / 100)), 2);
        $exchangeRate = max((float) $market->exchange_rate_to_bgn, 0.0001);
        $suggestedBgnEquivalent = round($suggestedNet * $exchangeRate, 2);
        $basePriceMarketCurrency = round((float) $configuration->base_price_bgn / $exchangeRate, 2);
        $marginAmount = round($suggestedBgnEquivalent - (float) $configuration->base_price_bgn, 2);
        $marginPercent = (float) $configuration->base_price_bgn > 0
            ? round(($marginAmount / (float) $configuration->base_price_bgn) * 100, 2)
            : 0.0;
        $status = $this->statusForMargin($marginPercent);
        $note = $this->competitionNote($status, $market->name, $avg, $count);
        $summary = sprintf(
            'Average benchmark %.2f %s incl. VAT across %d offers. Suggested net %.2f %s maps to %.2f BGN and %s.',
            $avg,
            $market->currency_code,
            $count,
            $suggestedNet,
            $market->currency_code,
            $suggestedBgnEquivalent,
            match ($status) {
                'viable' => 'looks commercially viable',
                'borderline' => 'needs tighter pricing review',
                default => 'does not currently justify expansion',
            }
        );

        return $this->persistResult($configuration, $market, [
            'reference_benchmark_count' => $count,
            'avg_benchmark_price' => $avg,
            'min_benchmark_price' => $min,
            'max_benchmark_price' => $max,
            'base_price_market_currency' => $basePriceMarketCurrency,
            'suggested_price_excluding_vat' => $suggestedNet,
            'suggested_price_including_vat' => $suggestedGross,
            'suggested_price_bgn_equivalent' => $suggestedBgnEquivalent,
            'target_margin_amount' => $marginAmount,
            'target_margin_percent' => $marginPercent,
            'viability_status' => $status,
            'competition_note' => $note,
            'analysis_summary' => $summary,
        ]);
    }

    private function persistResult(PricingConfiguration $configuration, PricingMarket $market, array $attributes): array
    {
        $attributes['calculated_at'] = now();

        PricingAnalysisResult::query()->updateOrCreate(
            [
                'pricing_configuration_id' => $configuration->id,
                'pricing_market_id' => $market->id,
            ],
            $attributes,
        );

        return $attributes;
    }

    private function statusForMargin(float $marginPercent): string
    {
        if ($marginPercent >= 10) {
            return 'viable';
        }

        if ($marginPercent >= 3) {
            return 'borderline';
        }

        return 'not_viable';
    }

    private function competitionNote(string $status, string $marketName, float $averagePrice, int $count): string
    {
        return match ($status) {
            'viable' => "Competitive headroom exists in {$marketName} based on {$count} active offers around {$averagePrice}.",
            'borderline' => "Competition in {$marketName} is tight. Review cost basis and benchmark freshness.",
            default => "Current {$marketName} benchmarks leave insufficient margin for a healthy rollout.",
        };
    }
}
