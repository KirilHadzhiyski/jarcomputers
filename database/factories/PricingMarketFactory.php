<?php

namespace Database\Factories;

use App\Models\PricingMarket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PricingMarket>
 */
class PricingMarketFactory extends Factory
{
    protected $model = PricingMarket::class;

    public function definition(): array
    {
        return [
            'name' => fake()->country(),
            'code' => strtoupper(fake()->unique()->lexify('??')),
            'currency_code' => 'EUR',
            'vat_rate' => fake()->randomFloat(2, 19, 25),
            'exchange_rate_to_bgn' => 1.9558,
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
