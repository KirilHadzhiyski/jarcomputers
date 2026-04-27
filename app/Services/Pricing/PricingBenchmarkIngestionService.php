<?php

namespace App\Services\Pricing;

use App\Models\PricingBenchmark;
use App\Models\PricingMarket;
use App\Models\User;

class PricingBenchmarkIngestionService
{
    public function ingest(array $payload, ?User $actor = null): PricingBenchmark
    {
        return PricingBenchmark::query()->create($this->attributesFromPayload($payload, $actor));
    }

    public function attributesFromPayload(array $payload, ?User $actor = null): array
    {
        $market = PricingMarket::query()->findOrFail($payload['pricing_market_id']);
        $priceIncludesVat = (bool) ($payload['price_includes_vat'] ?? true);
        $observedPrice = (float) $payload['observed_price'];
        $priceIncludingVat = $payload['price_including_vat'] ?? ($priceIncludesVat
            ? $observedPrice
            : round($observedPrice * (1 + ($market->vat_rate / 100)), 2));
        $priceExcludingVat = $payload['price_excluding_vat'] ?? ($priceIncludesVat
            ? round($priceIncludingVat / (1 + ($market->vat_rate / 100)), 2)
            : $observedPrice);

        return [
            'pricing_configuration_id' => $payload['pricing_configuration_id'],
            'pricing_market_id' => $market->id,
            'pricing_source_id' => $payload['pricing_source_id'],
            'observed_price' => $observedPrice,
            'currency_code' => $payload['currency_code'] ?? $market->currency_code,
            'price_includes_vat' => $priceIncludesVat,
            'price_excluding_vat' => $priceExcludingVat,
            'price_including_vat' => $priceIncludingVat,
            'availability_text' => $payload['availability_text'] ?? null,
            'competitor_name' => $payload['competitor_name'] ?? null,
            'product_title' => $payload['product_title'] ?? null,
            'product_url' => $payload['product_url'] ?? null,
            'input_method' => $payload['input_method'] ?? 'manual',
            'is_active' => (bool) ($payload['is_active'] ?? true),
            'collected_at' => $payload['collected_at'] ?? now(),
            'created_by' => $actor?->id,
        ];
    }
}
