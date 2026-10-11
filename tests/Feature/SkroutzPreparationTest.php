<?php

namespace Tests\Feature;

use App\Jobs\ImportSkroutzOrder;
use App\Models\IntegrationConnection;
use App\Models\PricingConfiguration;
use App\Models\SkroutzListing;
use App\Models\User;
use App\Services\Integrations\IntegrationRegistry;
use App\Services\Integrations\SkroutzCatalog;
use App\Services\Integrations\SkroutzConnector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SkroutzPreparationTest extends TestCase
{
    use RefreshDatabase;

    private function product(): array
    {
        return ['link' => 'https://shop.example.com/pc?a=1&b=2', 'image' => 'https://shop.example.com/pc.jpg', 'category' => 'Computers > Desktop PC',
            'manufacturer' => 'Test Brand', 'mpn' => 'PC-01', 'ean' => '4006381333931', 'description' => 'CPU & RAM',
            'price_with_vat' => 999.99, 'vat' => 24, 'quantity' => 2, 'availability' => 'Available from 1 to 3 days'];
    }

    public function test_drafts_can_be_saved_but_incomplete_products_cannot_be_approved(): void
    {
        $configuration = PricingConfiguration::factory()->create();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get('/admin/skroutz/configurations/'.$configuration->id)->assertOk();
        $this->put('/admin/skroutz/configurations/'.$configuration->id, ['name' => 'PC', 'sku' => 'PC-01', 'product' => ['category' => 'PCs']])->assertSessionHasNoErrors();
        $this->assertNotEmpty(app(SkroutzCatalog::class)->missing($configuration->fresh()));
        $this->put('/admin/skroutz/configurations/'.$configuration->id, ['name' => 'PC', 'sku' => 'PC-01', 'approved' => true, 'product' => ['category' => 'PCs']])->assertSessionHasErrors('product.link');
        $this->assertDatabaseHas('skroutz_listings', ['approved' => false]);
    }

    public function test_xml_and_batch_use_approved_products_correct_units_and_escape_text(): void
    {
        $configuration = PricingConfiguration::factory()->create(['name' => 'PC & Display', 'sku' => 'PC-01', 'status' => 'approved']);
        SkroutzListing::create(['pricing_configuration_id' => $configuration->id, 'approved' => true, 'attributes' => $this->product()]);
        PricingConfiguration::factory()->create(['status' => 'draft']);
        $catalog = app(SkroutzCatalog::class);
        $xml = simplexml_load_string($catalog->xml());
        $this->assertCount(1, $xml->products->product);
        $this->assertSame('PC & Display', (string) $xml->products->product->name);
        $this->assertSame('PC-01', (string) $xml->products->product->uid);
        $this->assertSame('999.99', (string) $xml->products->product->price);
        $this->assertSame('2', (string) $xml->products->product->quantity);
        $this->assertSame(0, $xml->products->product->price_with_vat->count());
        $this->assertSame(0, $xml->products->product->id->count());
        $this->assertSame(99999, $catalog->batch()['data'][0]['price']);
        $this->assertSame(2, $catalog->batch()['data'][0]['quantity']);
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/admin/skroutz/catalog.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $configuration->skroutzListing->update(['attributes' => [...$this->product(), 'quantity' => 0]]);
        $this->assertSame('0', (string) simplexml_load_string($catalog->xml())->products->product->quantity);
        $this->assertFalse($catalog->batch()['data'][0]['enabled']);
    }

    public function test_public_feed_and_webhook_are_off_until_configured_and_enforce_source_ip(): void
    {
        Bus::fake();
        $this->get('/feeds/skroutz.xml')->assertNotFound();
        $event = ['event_type' => 'new_order', 'order' => ['code' => '260923-1234567', 'state' => 'forged']];
        $this->postJson('/webhooks/skroutz', $event)->assertNotFound();
        IntegrationConnection::where('provider', 'skroutz')->first()->update(['enabled' => true, 'credentials' => ['token' => 'private-token']]);
        $this->postJson('/webhooks/skroutz', $event)->assertForbidden();
        config(['skroutz.webhook_allowed_ips' => ['127.0.0.1'], 'skroutz.feed_allowed_ips' => ['127.0.0.1'], 'skroutz.feed_enabled' => true]);
        $this->get('/feeds/skroutz.xml')->assertOk();
        $this->postJson('/webhooks/skroutz', $event)->assertOk();
        Bus::assertDispatched(ImportSkroutzOrder::class, fn ($job) => $job->code === '260923-1234567');
        $this->assertDatabaseCount('external_documents', 0);
    }

    public function test_order_import_verifies_api_response_and_never_accepts_or_changes_external_orders(): void
    {
        Http::preventStrayRequests();
        $connection = IntegrationConnection::where('provider', 'skroutz')->first();
        $connection->update(['enabled' => true, 'credentials' => ['token' => 'private-token']]);
        Http::fake(['api.skroutz.gr/*' => Http::response(['order' => ['code' => '260923-1234567', 'state' => 'open', 'line_items' => []]])]);
        app(SkroutzConnector::class)->order($connection, '260923-1234567');
        app(SkroutzConnector::class)->order($connection, '260923-1234567');
        $this->assertDatabaseCount('external_documents', 1);
        $this->assertDatabaseHas('external_documents', ['provider' => 'skroutz', 'external_id' => '260923-1234567', 'status' => 'open']);
        Http::assertSent(fn ($r) => $r->method() === 'GET' && $r->hasHeader('Authorization', 'Bearer private-token') && $r->hasHeader('Accept', 'application/vnd.skroutz+json; version=3.0'));
        Http::assertNotSent(fn ($r) => $r->method() !== 'GET');
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_products_api_validation_uses_its_own_token_and_never_publishes(): void
    {
        $configuration = PricingConfiguration::factory()->create(['name' => 'PC', 'sku' => 'PC-01', 'status' => 'approved']);
        SkroutzListing::create(['pricing_configuration_id' => $configuration->id, 'approved' => true, 'attributes' => $this->product()]);
        $connection = IntegrationConnection::where('provider', 'skroutz')->first();
        $connection->update(['credentials' => ['token' => 'orders-token', 'products_token' => 'products-token'], 'settings' => ['products_mode' => 'validation']]);
        Http::preventStrayRequests();
        Http::fake(['api.skroutz.gr/merchants/products/batch/validations' => Http::response(['success' => true, 'message' => 'All validations passed successfully.'])]);

        $this->assertSame(1, app(SkroutzConnector::class)->sync($connection->fresh()));
        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request->url() === 'https://api.skroutz.gr/merchants/products/batch/validations'
            && $request->hasHeader('Authorization', 'Bearer products-token')
            && $request['data'][0]['product_id'] === 'PC-01');
        Http::assertNotSent(fn ($request) => $request->url() === 'https://api.skroutz.gr/merchants/products/batch');
    }

    public function test_live_products_api_polls_pending_batch_before_sending_again(): void
    {
        $configuration = PricingConfiguration::factory()->create(['name' => 'PC', 'sku' => 'PC-01', 'status' => 'approved']);
        SkroutzListing::create(['pricing_configuration_id' => $configuration->id, 'approved' => true, 'attributes' => $this->product()]);
        $connection = IntegrationConnection::where('provider', 'skroutz')->first();
        $connection->update(['credentials' => ['token' => 'orders-token', 'products_token' => 'products-token'], 'settings' => ['products_mode' => 'live']]);
        Http::preventStrayRequests();
        Http::fake(function ($request) {
            if ($request->method() === 'POST') {
                return Http::response(['success' => true, 'id' => 'batch_123456']);
            }

            return Http::response(['id' => 'batch_123456', 'status' => 'in_progress', 'results' => []]);
        });

        $this->assertSame(1, app(SkroutzConnector::class)->sync($connection->fresh()));
        $this->assertSame(0, app(SkroutzConnector::class)->sync($connection->fresh()));
        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => $request->method() === 'GET' && str_ends_with($request->url(), '/products/batch/batch_123456/status'));
    }

    public function test_products_token_is_required_only_when_product_automation_is_selected(): void
    {
        $connection = IntegrationConnection::where('provider', 'skroutz')->first();
        $connection->update(['credentials' => ['token' => 'orders-token'], 'settings' => ['products_mode' => 'off']]);
        $this->assertSame([], IntegrationRegistry::missing($connection->fresh()));
        $connection->update(['settings' => ['products_mode' => 'validation']]);
        $this->assertSame(['Products API token'], IntegrationRegistry::missing($connection->fresh()));
    }
}
