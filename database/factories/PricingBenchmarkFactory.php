<?php

namespace Database\Factories;

use App\Models\PricingBenchmark;
use App\Models\PricingConfiguration;
use App\Models\PricingMarket;
use App\Models\PricingSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PricingBenchmark>
 */
class PricingBenchmarkFactory extends Factory
{
    protected $model = PricingBenchmark::class;

    public function definition(): array
    {
        return [
            'pricing_configuration_id' => PricingConfiguration::factory(),
            'pricing_market_id' => PricingMarket::factory(),
            'pricing_source_id' => function (array $attributes): int {
                return PricingSource::factory()->create([
                    'pricing_market_id' => $attributes['pricing_market_id'],
                ])->id;
            },
            'observed_price' => 720,
            'currency_code' => 'EUR',
            'price_includes_vat' => true,
            'price_excluding_vat' => 580.65,
            'price_including_vat' => 720,
            'availability_text' => 'In stock',
            'competitor_name' => 'Competitor Store',
            'product_title' => fake()->sentence(3),
            'product_url' => fake()->url(),
            'input_method' => 'manual',
            'is_active' => true,
            'collected_at' => now(),
        ];
    }
}
