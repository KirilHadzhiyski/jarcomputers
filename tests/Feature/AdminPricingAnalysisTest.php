<?php

namespace Tests\Feature;

use App\Models\PricingAnalysisResult;
use App\Models\PricingBenchmark;
use App\Models\PricingConfiguration;
use App\Models\PricingMarket;
use App\Models\PricingSource;
use App\Models\User;
use App\Services\Pricing\PricingAnalysisService;
use App\Services\Pricing\PricingBenchmarkIngestionService;
use App\Services\Pricing\PricingOpportunitySummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPricingAnalysisTest extends TestCase
{
    use RefreshDatabase;

    public function test_analysis_service_calculates_vat_currency_conversion_and_viability(): void
    {
        $market = PricingMarket::factory()->create([
            'name' => 'Greece',
            'code' => 'GR',
            'currency_code' => 'EUR',
            'vat_rate' => 24,
            'exchange_rate_to_bgn' => 1.9558,
        ]);
        $configuration = PricingConfiguration::factory()->create([
            'base_price_bgn' => 1000,
        ]);
        $source = PricingSource::factory()->create([
            'pricing_market_id' => $market->id,
            'source_key' => 'bestprice-gr',
        ]);

        PricingBenchmark::query()->create([
            'pricing_configuration_id' => $configuration->id,
            'pricing_market_id' => $market->id,
            'pricing_source_id' => $source->id,
            'observed_price' => 720,
            'currency_code' => 'EUR',
            'price_includes_vat' => true,
            'price_excluding_vat' => 580.65,
            'price_including_vat' => 720,
            'input_method' => 'manual',
            'is_active' => true,
        ]);

        PricingBenchmark::query()->create([
            'pricing_configuration_id' => $configuration->id,
            'pricing_market_id' => $market->id,
            'pricing_source_id' => $source->id,
            'observed_price' => 780,
            'currency_code' => 'EUR',
            'price_includes_vat' => true,
            'price_excluding_vat' => 629.03,
            'price_including_vat' => 780,
            'input_method' => 'manual',
            'is_active' => true,
        ]);

        $result = app(PricingAnalysisService::class)->analyze($configuration, $market);

        $this->assertSame('viable', $result['viability_status']);
        $this->assertSame(2, $result['reference_benchmark_count']);
        $this->assertEqualsWithDelta(750, $result['avg_benchmark_price'], 0.01);
        $this->assertEqualsWithDelta(604.84, $result['suggested_price_excluding_vat'], 0.02);
        $this->assertEqualsWithDelta(1182.95, $result['suggested_price_bgn_equivalent'], 0.05);
        $this->assertEqualsWithDelta(18.33, $result['target_margin_percent'], 0.1);
        $this->assertDatabaseHas('pricing_analysis_results', [
            'pricing_configuration_id' => $configuration->id,
            'pricing_market_id' => $market->id,
            'viability_status' => 'viable',
        ]);
    }

    public function test_ingestion_service_normalizes_vat_fields_and_marks_scraper_input(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $market = PricingMarket::factory()->create([
            'vat_rate' => 24,
            'exchange_rate_to_bgn' => 1.9558,
        ]);
        $configuration = PricingConfiguration::factory()->create();
        $source = PricingSource::factory()->create([
            'pricing_market_id' => $market->id,
        ]);

        $benchmark = app(PricingBenchmarkIngestionService::class)->ingest([
            'pricing_configuration_id' => $configuration->id,
            'pricing_market_id' => $market->id,
            'pricing_source_id' => $source->id,
            'observed_price' => 620,
            'currency_code' => 'EUR',
            'price_includes_vat' => true,
            'input_method' => 'scraper',
            'availability_text' => 'In stock',
        ], $admin);

        $this->assertSame('scraper', $benchmark->input_method);
        $this->assertEqualsWithDelta(500, (float) $benchmark->price_excluding_vat, 0.01);
        $this->assertEqualsWithDelta(620, (float) $benchmark->price_including_vat, 0.01);
        $this->assertSame($admin->id, $benchmark->created_by);
    }

    public function test_summary_service_counts_pricing_entities(): void
    {
        $market = PricingMarket::factory()->create([
            'is_active' => true,
        ]);
        $configuration = PricingConfiguration::factory()->create();
        $source = PricingSource::factory()->create([
            'pricing_market_id' => $market->id,
            'is_active' => true,
        ]);

        PricingBenchmark::query()->create([
            'pricing_configuration_id' => $configuration->id,
            'pricing_market_id' => $market->id,
            'pricing_source_id' => $source->id,
            'observed_price' => 720,
            'currency_code' => 'EUR',
            'price_includes_vat' => true,
            'price_excluding_vat' => 580.65,
            'price_including_vat' => 720,
            'input_method' => 'manual',
            'is_active' => true,
            'collected_at' => now(),
        ]);

        PricingAnalysisResult::query()->create([
            'pricing_configuration_id' => $configuration->id,
            'pricing_market_id' => $market->id,
            'reference_benchmark_count' => 1,
            'avg_benchmark_price' => 720,
            'min_benchmark_price' => 720,
            'max_benchmark_price' => 720,
            'suggested_price_excluding_vat' => 580.65,
            'suggested_price_including_vat' => 720,
            'suggested_price_bgn_equivalent' => 1135.65,
            'target_margin_amount' => 135.65,
            'target_margin_percent' => 13.56,
            'viability_status' => 'viable',
            'calculated_at' => now(),
        ]);

        $summary = app(PricingOpportunitySummaryService::class)->summary();

        $this->assertSame(1, $summary['configuration_count']);
        $this->assertSame(1, $summary['active_market_count']);
        $this->assertSame(1, $summary['active_source_count']);
        $this->assertSame(1, $summary['recent_benchmark_count']);
        $this->assertSame(1, $summary['viable_count']);
    }
}
