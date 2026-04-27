<?php

namespace Database\Factories;

use App\Models\PricingMarket;
use App\Models\PricingSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PricingSource>
 */
class PricingSourceFactory extends Factory
{
    protected $model = PricingSource::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['bestprice.gr', 'Skroutz', 'eMAG']);

        return [
            'pricing_market_id' => PricingMarket::factory(),
            'name' => $name,
            'source_key' => str($name)->lower()->replace('.', '-')->append('-'.fake()->unique()->numberBetween(1, 99))->value(),
            'base_url' => 'https://'.str($name)->lower()->value(),
            'input_type' => 'hybrid',
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
