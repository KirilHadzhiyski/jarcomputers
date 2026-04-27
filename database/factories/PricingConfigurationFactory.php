<?php

namespace Database\Factories;

use App\Models\PricingConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PricingConfiguration>
 */
class PricingConfigurationFactory extends Factory
{
    protected $model = PricingConfiguration::class;

    public function definition(): array
    {
        return [
            'name' => 'Gaming Config '.fake()->unique()->numberBetween(1, 99),
            'sku' => 'CFG-'.fake()->unique()->numberBetween(100, 999),
            'base_price_bgn' => fake()->randomFloat(2, 1200, 4200),
            'description' => fake()->sentence(),
            'component_summary' => fake()->sentence(6),
            'status' => 'draft',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
