<?php

namespace Tests\Feature;

use App\Models\PricingConfiguration;
use App\Models\SkroutzListing;
use App\Models\User;
use App\Services\Integrations\SkroutzCatalog;
use App\Services\Pricing\CrossBorderReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossBorderReviewTest extends TestCase
{
    use RefreshDatabase;

    private function scenario(): array
    {
        return [
            'name' => 'Reviewed PC', 'itemType' => 'configuration', 'targetMarket' => 'greece',
            'buyCost' => 500, 'localSalePrice' => 650, 'targetSalePrice' => 850, 'competitorPrice' => 900,
            'marketBenchmarkPrices' => ['greece' => 900, 'czechia' => 870, 'romania' => 880],
            'shippingCost' => 20, 'marketplaceFeePercent' => 8, 'minMarginPercent' => 12,
            'demandScore' => 7, 'competitionScore' => 5, 'notes' => 'Test', 'feeBasis' => 'gross',
            'benchmarkSourceUrl' => 'https://example.com/pc', 'benchmarkCheckedAt' => today()->toDateString(),
            'benchmarkConfidence' => 8, 'taxVerified' => true, 'reviewStatus' => 'approved',
        ];
    }

    public function test_fee_basis_changes_profit_without_subtracting_vat_twice(): void
    {
        $review = app(CrossBorderReview::class);
        $gross = $review->financials([...$this->scenario(), 'targetSalePrice' => 800]);
        $net = $review->financials([...$this->scenario(), 'targetSalePrice' => 800, 'feeBasis' => 'net']);
        $this->assertEqualsWithDelta(645.16129, $gross['net'], 0.00001);
        $this->assertEqualsWithDelta(64, $gross['fee'], 0.00001);
        $this->assertEqualsWithDelta(61.16129, $gross['profit'], 0.00001);
        $this->assertEqualsWithDelta(51.61290, $net['fee'], 0.00001);
        $this->assertEqualsWithDelta(73.54839, $net['profit'], 0.00001);
    }

    public function test_templates_are_separate_and_edits_revoke_approval(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->postJson('/admin/workspace/opportunities', [...$this->scenario(), 'isTemplate' => true])->assertOk();
        $id = $this->postJson('/admin/workspace/opportunities', $this->scenario())->assertOk()->assertJsonPath('reviewStatus', 'draft')->json('id');
        $this->getJson('/admin/workspace/data')->assertJsonCount(1, 'templates')->assertJsonCount(1, 'opportunities');
        $this->patchJson('/admin/workspace/opportunities/'.$id.'/review', ['status' => 'approved'])->assertOk()->assertJsonPath('reviewStatus', 'approved');
        SkroutzListing::create(['pricing_configuration_id' => $id, 'attributes' => [], 'approved' => true]);
        $this->putJson('/admin/workspace/opportunities/'.$id, [...$this->scenario(), 'buyCost' => 510])->assertOk()->assertJsonPath('reviewStatus', 'draft');
        $this->assertDatabaseHas('skroutz_listings', ['pricing_configuration_id' => $id, 'approved' => false]);
    }

    public function test_stale_unknown_or_unprofitable_scenarios_cannot_be_approved(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach ([['benchmarkCheckedAt' => today()->subDays(15)->toDateString()], ['benchmarkConfidence' => 2], ['taxVerified' => false], ['targetSalePrice' => 300], ['isTemplate' => true]] as $change) {
            $id = $this->postJson('/admin/workspace/opportunities', [...$this->scenario(), ...$change])->assertOk()->json('id');
            $this->patchJson('/admin/workspace/opportunities/'.$id.'/review', ['status' => 'approved'])->assertUnprocessable()->assertJsonValidationErrors('status');
        }
    }

    public function test_skroutz_export_expires_reviews_and_rejects_price_drift(): void
    {
        $configuration = PricingConfiguration::factory()->create(['status' => 'approved', 'cross_border_data' => $this->scenario()]);
        $product = ['link' => 'https://example.com/pc', 'image' => 'https://example.com/pc.jpg', 'category' => 'PCs',
            'manufacturer' => 'PC', 'mpn' => 'PC-1', 'ean' => '4006381333931', 'description' => 'Custom PC',
            'price_with_vat' => 850, 'vat' => 24, 'quantity' => 1, 'availability' => 'Available'];
        $listing = SkroutzListing::create(['pricing_configuration_id' => $configuration->id, 'approved' => true, 'attributes' => $product]);
        $catalog = app(SkroutzCatalog::class);
        $this->assertCount(1, $catalog->batch()['data']);
        $listing->update(['attributes' => [...$product, 'price_with_vat' => 300]]);
        $this->assertCount(0, $catalog->batch()['data']);
        $this->actingAs(User::factory()->create(['role' => 'admin']))->put('/admin/skroutz/configurations/'.$configuration->id, [
            'name' => $configuration->name, 'sku' => $configuration->sku, 'approved' => true,
            'product' => [...$product, 'price_with_vat' => 300],
        ])->assertSessionHasErrors('approved');
        $listing->update(['attributes' => $product]);
        $this->travel(15)->days();
        $this->assertCount(0, $catalog->batch()['data']);
    }
}
