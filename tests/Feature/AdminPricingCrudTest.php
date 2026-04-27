<?php

namespace Tests\Feature;

use App\Models\PricingBenchmark;
use App\Models\PricingConfiguration;
use App\Models\PricingMarket;
use App\Models\PricingSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPricingCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_pricing_sections(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get('/admin/pricing')
            ->assertOk()
            ->assertSee('Pricing intelligence');

        $this->actingAs($admin)
            ->get('/admin/pricing/configurations')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/pricing/markets')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/pricing/sources')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/pricing/analysis')
            ->assertOk()
            ->assertSee('Can this configuration be sold competitively in the selected market?');
    }

    public function test_regular_user_cannot_access_pricing_admin(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $this->actingAs($user)
            ->get('/admin/pricing')
            ->assertForbidden();
    }

    public function test_admin_can_create_pricing_entities_and_run_analysis(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $configurationResponse = $this->actingAs($admin)->post('/admin/pricing/configurations', [
            'name' => 'Gaming Beast',
            'sku' => 'GB-01',
            'base_price_bgn' => 1000,
            'status' => 'draft',
            'description' => 'Gaming desktop bundle',
            'component_summary' => 'RTX 4070, Ryzen 7, 32GB RAM',
            'notes' => 'First pricing candidate',
        ]);

        $configuration = PricingConfiguration::query()->where('sku', 'GB-01')->firstOrFail();

        $configurationResponse->assertRedirect(route('admin.pricing.configurations.edit', $configuration));

        $marketResponse = $this->actingAs($admin)->post('/admin/pricing/markets', [
            'name' => 'Greece',
            'code' => 'GR',
            'currency_code' => 'EUR',
            'vat_rate' => 24,
            'exchange_rate_to_bgn' => 1.9558,
            'is_active' => '1',
            'notes' => 'Primary export market',
        ]);

        $market = PricingMarket::query()->where('code', 'GR')->firstOrFail();

        $marketResponse->assertRedirect(route('admin.pricing.markets.edit', $market));

        $sourceResponse = $this->actingAs($admin)->post('/admin/pricing/sources', [
            'pricing_market_id' => $market->id,
            'name' => 'bestprice.gr',
            'source_key' => 'bestprice-gr',
            'base_url' => 'https://www.bestprice.gr',
            'input_type' => 'hybrid',
            'is_active' => '1',
            'notes' => 'Main Greek source',
        ]);

        $source = PricingSource::query()->where('source_key', 'bestprice-gr')->firstOrFail();

        $sourceResponse->assertRedirect(route('admin.pricing.sources.edit', $source));

        $benchmarkResponse = $this->actingAs($admin)->post('/admin/pricing/benchmarks', [
            'pricing_configuration_id' => $configuration->id,
            'pricing_market_id' => $market->id,
            'pricing_source_id' => $source->id,
            'observed_price' => 750,
            'currency_code' => 'EUR',
            'price_includes_vat' => '1',
            'availability_text' => 'In stock',
            'competitor_name' => 'bestprice.gr',
            'product_title' => 'Gaming Beast offer',
            'product_url' => 'https://www.bestprice.gr/item/example',
            'input_method' => 'manual',
            'is_active' => '1',
            'collected_at' => now()->toDateTimeString(),
        ]);

        $benchmark = PricingBenchmark::query()->where('pricing_configuration_id', $configuration->id)->firstOrFail();

        $benchmarkResponse->assertRedirect(route('admin.pricing.benchmarks.edit', $benchmark));

        $analysisResponse = $this->actingAs($admin)->post('/admin/pricing/analysis/run', [
            'pricing_configuration_id' => $configuration->id,
            'pricing_market_id' => $market->id,
        ]);

        $analysisResponse->assertRedirect(route('admin.pricing.analysis.index', [
            'configuration_id' => $configuration->id,
            'market_id' => $market->id,
        ]));

        $this->assertDatabaseHas('pricing_analysis_results', [
            'pricing_configuration_id' => $configuration->id,
            'pricing_market_id' => $market->id,
            'viability_status' => 'viable',
        ]);
    }

    public function test_admin_can_run_sample_sync_endpoint(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $configuration = PricingConfiguration::factory()->create([
            'base_price_bgn' => 1200,
        ]);
        $market = PricingMarket::factory()->create([
            'code' => 'GR',
            'currency_code' => 'EUR',
            'vat_rate' => 24,
            'exchange_rate_to_bgn' => 1.9558,
        ]);
        $source = PricingSource::factory()->create([
            'pricing_market_id' => $market->id,
            'source_key' => 'skroutz-gr',
        ]);

        $response = $this->actingAs($admin)->post('/admin/pricing/sync', [
            'pricing_configuration_id' => $configuration->id,
            'pricing_market_id' => $market->id,
            'pricing_source_id' => $source->id,
        ]);

        $response->assertRedirect(route('admin.pricing.dashboard'));
        $this->assertDatabaseHas('pricing_benchmarks', [
            'pricing_configuration_id' => $configuration->id,
            'pricing_market_id' => $market->id,
            'pricing_source_id' => $source->id,
            'input_method' => 'scraper',
        ]);
    }
}
