<?php

namespace Tests\Feature;

use App\Jobs\SyncIntegration;
use App\Models\ConversationMessage;
use App\Models\IntegrationConnection;
use App\Models\PricingConfiguration;
use App\Models\PricingMarket;
use App\Models\PricingSource;
use App\Models\User;
use App\Services\Integrations\EcbConnector;
use App\Services\Integrations\GoogleReviewsConnector;
use App\Services\Integrations\IntegrationException;
use App\Services\Integrations\IntegrationManager;
use App\Services\Integrations\JarConnector;
use App\Services\Integrations\MailboxConnector;
use App\Services\Integrations\PriceFeedConnector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminIntegrationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    private function connection(string $provider, array $settings = [], array $credentials = []): IntegrationConnection
    {
        $connection = IntegrationConnection::where('provider', $provider)->firstOrFail();
        $connection->update(['settings' => $settings, 'credentials' => $credentials]);

        return $connection;
    }

    public function test_cpanel_polling_floor_preserves_slower_provider_intervals(): void
    {
        config(['integrations.polling_floor_minutes' => 15]);
        $definitions = \App\Services\Integrations\IntegrationRegistry::definitions();
        $this->assertSame(15, $definitions['mailbox']['interval']);
        $this->assertSame(1440, $definitions['ecb']['interval']);
    }

    public function test_pending_cron_jobs_are_not_expired_as_running_jobs(): void
    {
        Bus::fake();
        $connection = $this->connection('ecb');
        $run = $connection->runs()->create(['status' => 'queued', 'created_at' => now()->subMinutes(30)]);
        $manager = app(IntegrationManager::class);
        $this->assertNull($manager->enqueue($connection));
        $this->assertSame('queued', $run->fresh()->status);
        $run->update(['status' => 'running', 'started_at' => now()]);
        $this->assertNull($manager->enqueue($connection));
        $this->travel(21)->minutes();
        $this->assertNotNull($manager->enqueue($connection));
        $this->assertSame('failed', $run->fresh()->status);
    }

    public function test_admin_screens_are_protected_and_render_with_no_data(): void
    {
        foreach (['integrations', 'inbox', 'supplier-catalog', 'external-documents', 'skroutz'] as $path) {
            $this->get('/admin/'.$path)->assertRedirect('/login');
        }
        $this->actingAs(User::factory()->create(['role' => 'user']))->get('/admin/integrations')->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach (['integrations', 'inbox', 'supplier-catalog', 'external-documents', 'skroutz'] as $path) {
            $this->get('/admin/'.$path)->assertOk();
        }
    }

    public function test_secrets_are_encrypted_not_rendered_and_blank_fields_preserve_them(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $payload = ['enabled' => true, 'credentials' => ['username' => 'test-account', 'password' => 'private-test-secret'], 'settings' => ['currency' => 'EUR']];
        $this->from('/admin/integrations')->put('/admin/integrations/jar', $payload)->assertSessionHasNoErrors();
        $connection = IntegrationConnection::where('provider', 'jar')->firstOrFail();
        $this->assertSame('private-test-secret', $connection->credentials['password']);
        $this->assertStringNotContainsString('private-test-secret', DB::table('integration_connections')->where('provider', 'jar')->value('credentials'));
        $this->get('/admin/integrations')->assertDontSee('private-test-secret');
        $payload['credentials'] = ['username' => '', 'password' => ''];
        $this->put('/admin/integrations/jar', $payload)->assertSessionHasNoErrors();
        $this->assertSame('private-test-secret', $connection->fresh()->credentials['password']);
        $this->put('/admin/integrations/jar', ['enabled' => true, 'clear_credentials' => true])->assertSessionHasErrors('integration');
        $this->assertFalse(session()->has('_old_input.credentials'));
    }

    public function test_switching_mailbox_resets_cursor_and_rejects_unknown_feed_hosts(): void
    {
        $connection = $this->connection('mailbox', ['service' => 'gmail', 'mailbox' => 'old@example.com']);
        $connection->update(['state' => ['mail_until' => now()->toIso8601String()], 'last_success_at' => now()]);
        $this->actingAs(User::factory()->create(['role' => 'admin']))->put('/admin/integrations/mailbox', ['settings' => ['service' => 'microsoft', 'mailbox' => 'new@example.com']])->assertSessionHasNoErrors();
        $this->assertSame([], $connection->fresh()->state);
        $this->assertNull($connection->fresh()->last_success_at);
        $this->put('/admin/integrations/price_feed', ['settings' => ['url' => 'https://127.0.0.1/private']])->assertSessionHasErrors('integration');
        Http::assertNothingSent();
    }

    public function test_queue_deduplicates_pending_runs_and_failure_is_redacted(): void
    {
        Bus::fake();
        $connection = $this->connection('jar', ['currency' => 'EUR'], ['username' => 'example', 'password' => 'secret']);
        $manager = app(IntegrationManager::class);
        $run = $manager->enqueue($connection);
        $this->assertNotNull($run);
        $this->assertNull($manager->enqueue($connection));
        Bus::assertDispatched(SyncIntegration::class);
        Http::fake(['api.jarcomputers.com/*' => Http::response(['error' => 'sensitive provider body: secret'], 401)]);
        (new SyncIntegration($run->id))->handle($manager);
        $this->assertSame('failed', $run->fresh()->status);
        $this->assertStringNotContainsString('secret', $run->fresh()->message);
        $this->assertNull($connection->fresh()->last_success_at);
        $this->assertDatabaseCount('supplier_products', 0);
    }

    public function test_jar_pages_and_short_updates_are_idempotent_and_do_not_invent_local_stock(): void
    {
        $connection = $this->connection('jar', ['currency' => 'EUR'], ['username' => 'example', 'password' => 'secret']);
        Http::fake(function ($request) {
            if (str_contains($request->url(), 'products_short')) {
                return Http::response(['pages' => ['cnt' => 1], 'products' => ['A' => ['code' => 'A', 'price' => 12, 'avail' => 'Няма наличност']]]);
            }
            if (str_contains($request->url(), '/products?')) {
                $code = $request['page'] == 1 ? 'A' : 'B';

                return Http::response(['pages' => ['cnt' => 2], 'products' => [$code => ['code' => $code, 'product_name' => 'Product '.$code, 'price' => 10, 'dealer_price' => 8, 'avail' => 'Централен склад']]]);
            }

            return Http::response(['item' => [['pid' => 7, 'id' => 7, 'total' => 10, 'edate' => now()->toDateString(), 'status' => 'open']]]);
        });
        $this->assertSame(5, app(JarConnector::class)->sync($connection));
        $this->assertSame(4, app(JarConnector::class)->sync($connection->fresh()));
        $this->assertDatabaseCount('supplier_products', 2);
        $this->assertDatabaseCount('external_documents', 3);
        $this->assertDatabaseCount('inventory_items', 0);
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseHas('supplier_products', ['code' => 'A', 'name' => 'Product A', 'retail_price' => 12, 'currency_code' => 'EUR']);
        Http::assertSent(fn ($r) => $r->hasHeader('Authorization', 'Basic '.base64_encode('example:secret')) && str_starts_with($r->url(), 'https://api.jarcomputers.com/'));
    }

    public function test_ecb_namespaced_xml_updates_rates_and_rejects_stale_snapshot(): void
    {
        $market = PricingMarket::factory()->create(['currency_code' => 'USD']);
        $xml = '<?xml version="1.0"?><gesmes:Envelope xmlns:gesmes="http://www.gesmes.org/xml/2002-08-01" xmlns="http://www.ecb.int/vocabulary/2002-08-01/eurofxref"><Cube><Cube time="'.now()->toDateString().'"><Cube currency="USD" rate="1.2"/><Cube currency="RON" rate="5.1"/></Cube></Cube></gesmes:Envelope>';
        Http::fake(['www.ecb.europa.eu/*' => Http::sequence()->push($xml)->push(str_replace(now()->toDateString(), '2000-01-01', $xml))]);
        app(EcbConnector::class)->sync($this->connection('ecb'));
        $this->assertDatabaseHas('exchange_rates', ['currency_code' => 'BGN', 'units_per_eur' => 1.95583]);
        $this->assertEqualsWithDelta(1.95583 / 1.2, (float) $market->fresh()->exchange_rate_to_bgn, 0.0001);
        try {
            app(EcbConnector::class)->sync($this->connection('ecb'));
            $this->fail('Old rates must fail.');
        } catch (IntegrationException) {
            $this->assertDatabaseHas('exchange_rates', ['currency_code' => 'USD', 'as_of' => now()->toDateString()]);
        }
    }

    public function test_price_feed_uses_real_identifiers_and_rolls_back_a_mismatched_batch(): void
    {
        config(['integrations.feed_hosts' => ['prices.example.com']]);
        $connection = $this->connection('price_feed', ['url' => 'https://prices.example.com/offers']);
        $configuration = PricingConfiguration::factory()->create(['sku' => 'PC-01']);
        $market = PricingMarket::factory()->create(['code' => 'GR']);
        $source = PricingSource::factory()->create(['pricing_market_id' => $market->id, 'source_key' => 'authorized']);
        $offer = ['id' => 'offer-1', 'sku' => $configuration->sku, 'market' => 'GR', 'source_key' => $source->source_key,
            'price' => 700, 'currency' => 'EUR', 'includes_vat' => true, 'collected_at' => now()->toIso8601String(), 'url' => 'https://merchant.example.com/pc'];
        Http::fake(['prices.example.com/*' => Http::sequence()->push(['offers' => [$offer]])->push(['offers' => [$offer]])->push(['offers' => [[...$offer, 'price' => 999], [...$offer, 'id' => 'offer-2', 'sku' => 'UNKNOWN']]])]);
        app(PriceFeedConnector::class)->sync($connection);
        app(PriceFeedConnector::class)->sync($connection);
        $this->assertDatabaseCount('pricing_benchmarks', 1);
        $this->assertDatabaseHas('pricing_analysis_results', ['reference_benchmark_count' => 1]);
        try {
            app(PriceFeedConnector::class)->sync($connection);
            $this->fail('Unknown SKU must fail.');
        } catch (IntegrationException) {
            $this->assertDatabaseHas('pricing_benchmarks', ['external_id' => 'offer-1', 'observed_price' => 700]);
        }
        $this->travel(8)->days();
        $this->artisan('pricing:refresh')->assertSuccessful();
        $this->assertDatabaseHas('pricing_analysis_results', ['reference_benchmark_count' => 0]);
    }

    public function test_google_reviews_import_as_drafts_without_duplicate_or_publication_changes(): void
    {
        $connection = $this->connection('google_reviews', ['account_id' => '123', 'location_id' => '456'], ['client_id' => 'c', 'client_secret' => 's', 'refresh_token' => 'r']);
        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'access']),
            'mybusiness.googleapis.com/*' => Http::response(['totalReviewCount' => 1, 'averageRating' => 5, 'reviews' => [['reviewId' => 'review-1', 'starRating' => 'FIVE', 'reviewer' => ['displayName' => 'Customer'], 'comment' => 'Great', 'createTime' => now()->toIso8601String()]]]),
        ]);
        app(GoogleReviewsConnector::class)->sync($connection);
        app(GoogleReviewsConnector::class)->sync($connection);
        $this->assertDatabaseCount('customer_reviews', 1);
        $this->assertDatabaseHas('customer_reviews', ['external_id' => 'review-1', 'is_published' => false, 'rating' => 5]);
    }

    public function test_mailbox_import_is_read_only_and_deduplicates_messages(): void
    {
        $connection = $this->connection('mailbox', ['service' => 'gmail', 'mailbox' => 'office@example.com'], ['client_id' => 'c', 'client_secret' => 's', 'refresh_token' => 'r']);
        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'access']),
            'gmail.googleapis.com/*/messages/*' => Http::response(['id' => 'mail-1', 'threadId' => 'thread-1', 'internalDate' => now()->getTimestampMs(), 'payload' => [
                'headers' => [['name' => 'From', 'value' => 'Customer <customer@example.com>'], ['name' => 'Subject', 'value' => 'Repair']],
                'mimeType' => 'multipart/alternative', 'parts' => [
                    ['mimeType' => 'text/plain', 'body' => ['data' => base64_encode('Plain body')]],
                    ['mimeType' => 'text/html', 'body' => ['data' => base64_encode('<b>Duplicate HTML body</b>')]],
                ],
            ]]),
            'gmail.googleapis.com/*' => Http::response(['messages' => [['id' => 'mail-1']], 'resultSizeEstimate' => 1]),
        ]);
        $this->assertSame(1, app(MailboxConnector::class)->sync($connection));
        $this->assertSame(0, app(MailboxConnector::class)->sync($connection->fresh()));
        $this->assertDatabaseCount('conversation_messages', 1);
        $this->assertDatabaseCount('customer_profiles', 1);
        $this->assertSame("Repair\n\nPlain body", ConversationMessage::first()->content);
        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'gmail.googleapis.com') && $r->method() !== 'GET');
    }

    public function test_microsoft_refresh_rotation_is_saved_and_nextlink_cannot_leak_token(): void
    {
        $connection = $this->connection('mailbox', ['service' => 'microsoft', 'mailbox' => 'office@example.com'], ['client_id' => 'c', 'client_secret' => 's', 'refresh_token' => 'old']);
        Http::fake([
            'login.microsoftonline.com/*' => Http::response(['access_token' => 'access', 'refresh_token' => 'rotated']),
            'graph.microsoft.com/*' => Http::response(['value' => [], '@odata.nextLink' => 'https://attacker.example.com/token']),
        ]);
        try {
            app(MailboxConnector::class)->sync($connection);
            $this->fail('Unexpected nextLink must fail.');
        } catch (IntegrationException) {
            $this->assertSame('rotated', $connection->fresh()->credentials['refresh_token']);
            $this->assertEmpty($connection->fresh()->state);
        }
        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'attacker.example.com'));
    }
}
