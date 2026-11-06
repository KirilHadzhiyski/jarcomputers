<?php

namespace Tests\Feature;

use App\Jobs\ExecuteWorkflow;
use App\Models\ClientSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WorkflowExecutionEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_json_workflow_collects_normalizes_filters_and_never_publishes(): void
    {
        config(['workflows.allowed_hosts' => ['feed.example.com']]);
        Http::fake([
            'https://feed.example.com/products' => Http::response([
                'products' => [
                    ['id' => 'p-1', 'sku' => 'LAP-1', 'name' => 'Laptop One', 'category' => 'Лаптопи', 'price' => '1299,90', 'currency' => 'eur', 'stock_status' => 'in_stock'],
                    ['id' => 'p-2', 'sku' => 'PHONE-1', 'name' => 'Phone One', 'category' => 'Телефони', 'price' => 999, 'currency' => 'EUR'],
                ],
            ]),
        ]);
        $client = User::factory()->create(['role' => 'user']);
        $this->subscribe($client, records: 100);
        $workflow = Workflow::query()->create([
            'client_user_id' => $client->id,
            'name' => 'Product feed',
            'slug' => 'product-feed',
            'status' => 'active',
            'trigger_type' => 'manual',
            'source_type' => 'rest_api',
            'source_url' => 'https://feed.example.com/products',
            'source_auth_type' => 'none',
            'category_filters' => ['Лаптопи'],
            'approval_mode' => 'automatic',
        ]);
        $workflow->steps()->create([
            'position' => 1,
            'type' => 'collect',
            'name' => 'Collect products',
            'configuration' => ['items_path' => 'products'],
            'is_enabled' => true,
        ]);
        $workflow->steps()->create([
            'position' => 2,
            'type' => 'publish',
            'name' => 'Marketplace publish',
            'is_enabled' => true,
        ]);
        $run = $workflow->runs()->create(['status' => 'queued', 'trigger_type' => 'manual']);

        app()->call([new ExecuteWorkflow($run->id), 'handle']);

        $run->refresh();
        $this->assertSame('completed', $run->status);
        $this->assertSame(1, $run->records_collected);
        $this->assertSame(1, $run->records_matched);
        $this->assertSame(0, $run->records_exported);
        $this->assertTrue($run->metrics['publish_blocked']);
        $this->assertDatabaseHas('workflow_records', [
            'workflow_run_id' => $run->id,
            'sku' => 'LAP-1',
            'title' => 'Laptop One',
            'currency_code' => 'EUR',
            'price' => 1299.9000,
        ]);
        $this->assertDatabaseMissing('workflow_records', ['sku' => 'PHONE-1']);
        $this->assertDatabaseHas('usage_records', ['workflow_run_id' => $run->id, 'service_type' => 'workflow', 'quantity' => 1]);
        $this->assertDatabaseHas('workflow_alerts', ['workflow_run_id' => $run->id, 'type' => 'approval_required', 'severity' => 'info']);
    }

    public function test_unapproved_source_host_fails_without_sending_request(): void
    {
        config(['workflows.allowed_hosts' => ['allowed.example.com']]);
        Http::fake();
        $workflow = Workflow::query()->create([
            'name' => 'Blocked source',
            'slug' => 'blocked-source',
            'status' => 'active',
            'trigger_type' => 'manual',
            'source_type' => 'rest_api',
            'source_url' => 'https://internal.example.test/secrets',
            'source_auth_type' => 'none',
            'approval_mode' => 'review',
        ]);
        $run = $workflow->runs()->create(['status' => 'queued', 'trigger_type' => 'manual']);

        app()->call([new ExecuteWorkflow($run->id), 'handle']);

        $this->assertSame('failed', $run->fresh()->status);
        $this->assertDatabaseHas('workflow_alerts', ['workflow_run_id' => $run->id, 'type' => 'execution_failed', 'severity' => 'critical']);
        Http::assertNothingSent();
    }

    public function test_plan_record_limit_cancels_run_without_partial_data(): void
    {
        config(['workflows.allowed_hosts' => ['feed.example.com']]);
        Http::fake([
            'https://feed.example.com/items' => Http::response([
                ['id' => 1, 'title' => 'One'],
                ['id' => 2, 'title' => 'Two'],
            ]),
        ]);
        $client = User::factory()->create(['role' => 'user']);
        $this->subscribe($client, records: 1);
        $workflow = Workflow::query()->create([
            'client_user_id' => $client->id,
            'name' => 'Limited feed',
            'slug' => 'limited-feed',
            'status' => 'active',
            'trigger_type' => 'manual',
            'source_type' => 'rest_api',
            'source_url' => 'https://feed.example.com/items',
            'source_auth_type' => 'none',
            'approval_mode' => 'review',
        ]);
        $run = $workflow->runs()->create(['status' => 'queued', 'trigger_type' => 'manual']);

        app()->call([new ExecuteWorkflow($run->id), 'handle']);

        $this->assertSame('cancelled', $run->fresh()->status);
        $this->assertDatabaseCount('workflow_records', 0);
        $this->assertDatabaseHas('workflow_alerts', ['workflow_run_id' => $run->id, 'type' => 'quota_exceeded']);
    }

    public function test_paused_subscription_blocks_workflow_before_external_request(): void
    {
        config(['workflows.allowed_hosts' => ['feed.example.com']]);
        Http::fake();
        $client = User::factory()->create(['role' => 'user']);
        $this->subscribe($client, records: 100);
        $client->currentSubscription()->firstOrFail()->update(['status' => 'paused']);
        $workflow = Workflow::query()->create([
            'client_user_id' => $client->id,
            'name' => 'Paused client feed', 'slug' => 'paused-client-feed', 'status' => 'active', 'trigger_type' => 'manual',
            'source_type' => 'rest_api', 'source_url' => 'https://feed.example.com/items', 'source_auth_type' => 'none', 'approval_mode' => 'review',
        ]);
        $run = $workflow->runs()->create(['status' => 'queued', 'trigger_type' => 'manual']);

        app()->call([new ExecuteWorkflow($run->id), 'handle']);

        $this->assertSame('cancelled', $run->fresh()->status);
        Http::assertNothingSent();
        $this->assertDatabaseHas('workflow_alerts', ['workflow_run_id' => $run->id, 'type' => 'quota_exceeded']);
    }

    public function test_rss_and_csv_sources_are_parsed_without_external_writes(): void
    {
        config(['workflows.allowed_hosts' => ['feed.example.com']]);
        Http::fake([
            'https://feed.example.com/news.xml' => Http::response('<?xml version="1.0"?><rss><channel><item><guid>n-1</guid><title>Market update</title><category>News</category><link>https://feed.example.com/n-1</link></item></channel></rss>', 200, ['Content-Type' => 'application/rss+xml']),
            'https://feed.example.com/products.csv' => Http::response("sku;title;price;currency_code\nCSV-1;CSV Product;25.50;EUR", 200, ['Content-Type' => 'text/csv']),
        ]);

        $rss = Workflow::query()->create([
            'name' => 'RSS source', 'slug' => 'rss-source', 'status' => 'active', 'trigger_type' => 'manual',
            'source_type' => 'rss', 'source_url' => 'https://feed.example.com/news.xml', 'source_auth_type' => 'none', 'approval_mode' => 'review',
        ]);
        $rssRun = $rss->runs()->create(['status' => 'queued', 'trigger_type' => 'manual']);
        app()->call([new ExecuteWorkflow($rssRun->id), 'handle']);

        $csv = Workflow::query()->create([
            'name' => 'CSV source', 'slug' => 'csv-source', 'status' => 'active', 'trigger_type' => 'manual',
            'source_type' => 'file_feed', 'source_url' => 'https://feed.example.com/products.csv', 'source_auth_type' => 'none', 'approval_mode' => 'review',
        ]);
        $csv->steps()->create(['position' => 1, 'type' => 'collect', 'name' => 'CSV', 'configuration' => ['delimiter' => ';'], 'is_enabled' => true]);
        $csvRun = $csv->runs()->create(['status' => 'queued', 'trigger_type' => 'manual']);
        app()->call([new ExecuteWorkflow($csvRun->id), 'handle']);

        $this->assertSame('completed', $rssRun->fresh()->status);
        $this->assertSame('completed', $csvRun->fresh()->status);
        $this->assertDatabaseHas('workflow_records', ['workflow_run_id' => $rssRun->id, 'external_id' => 'n-1', 'title' => 'Market update']);
        $this->assertDatabaseHas('workflow_records', ['workflow_run_id' => $csvRun->id, 'sku' => 'CSV-1', 'title' => 'CSV Product', 'price' => 25.5000]);
    }

    public function test_scheduler_queues_only_one_due_run(): void
    {
        Queue::fake();
        $workflow = Workflow::query()->create([
            'name' => 'Scheduled feed',
            'slug' => 'scheduled-feed',
            'status' => 'active',
            'trigger_type' => 'schedule',
            'frequency_minutes' => 60,
            'source_type' => 'rss',
            'source_url' => 'https://feed.example.com/rss',
            'source_auth_type' => 'none',
            'approval_mode' => 'review',
            'next_run_at' => now()->subMinute(),
        ]);

        $this->artisan('workflows:dispatch')->assertSuccessful();
        $this->artisan('workflows:dispatch')->assertSuccessful();

        $this->assertDatabaseCount('workflow_runs', 1);
        $this->assertSame('schedule', WorkflowRun::query()->firstOrFail()->trigger_type);
        Queue::assertPushedOn('integrations', ExecuteWorkflow::class);
        $this->assertTrue($workflow->fresh()->next_run_at->isFuture());
    }

    public function test_signed_webhook_queues_workflow_and_rejects_invalid_signature(): void
    {
        Queue::fake();
        $workflow = Workflow::query()->create([
            'name' => 'Webhook feed',
            'slug' => 'webhook-feed',
            'status' => 'active',
            'trigger_type' => 'webhook',
            'source_type' => 'rest_api',
            'source_url' => 'https://feed.example.com/items',
            'source_auth_type' => 'none',
            'webhook_secret' => 'webhook-test-secret',
            'approval_mode' => 'review',
        ]);
        $body = '{"event":"refresh"}';
        $timestamp = (string) now()->timestamp;

        $this->call('POST', route('workflows.webhook', $workflow), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_JAR_TIMESTAMP' => $timestamp,
            'HTTP_X_JAR_SIGNATURE' => 'sha256=invalid',
        ], $body)->assertUnauthorized();
        $this->assertDatabaseCount('workflow_runs', 0);

        $signature = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$body, 'webhook-test-secret');
        $this->call('POST', route('workflows.webhook', $workflow), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_JAR_TIMESTAMP' => $timestamp,
            'HTTP_X_JAR_SIGNATURE' => $signature,
        ], $body)->assertAccepted()->assertJson(['status' => 'queued']);

        $this->assertDatabaseHas('workflow_runs', ['workflow_id' => $workflow->id, 'trigger_type' => 'webhook', 'status' => 'queued']);
        Queue::assertPushedOn('integrations', ExecuteWorkflow::class);
    }

    public function test_workflow_credentials_are_encrypted_preserved_and_not_rendered(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->post('/admin/workflows', [
            'name' => 'Private feed',
            'status' => 'active',
            'trigger_type' => 'manual',
            'source_type' => 'rest_api',
            'source_url' => 'https://feed.example.com/private',
            'source_auth_type' => 'bearer',
            'source_token' => 'very-secret-workflow-token',
            'approval_mode' => 'review',
        ]);
        $workflow = Workflow::query()->firstOrFail();
        $response->assertRedirect(route('admin.workflows.edit', $workflow));
        $stored = (string) DB::table('workflows')->where('id', $workflow->id)->value('source_credentials');
        $this->assertStringNotContainsString('very-secret-workflow-token', $stored);
        $this->assertSame('very-secret-workflow-token', $workflow->fresh()->source_credentials['token']);

        $this->actingAs($admin)->put(route('admin.workflows.update', $workflow), [
            'name' => 'Private feed updated',
            'status' => 'active',
            'trigger_type' => 'manual',
            'source_type' => 'rest_api',
            'source_url' => 'https://feed.example.com/private',
            'source_auth_type' => 'bearer',
            'source_token' => '',
            'approval_mode' => 'review',
        ])->assertSessionHasNoErrors();

        $this->assertSame('very-secret-workflow-token', $workflow->fresh()->source_credentials['token']);
        $this->actingAs($admin)->get(route('admin.workflows.edit', $workflow))
            ->assertOk()
            ->assertDontSee('very-secret-workflow-token');
    }

    private function subscribe(User $client, int $records): void
    {
        $plan = SubscriptionPlan::query()->create([
            'name' => 'Engine plan '.$client->id,
            'code' => 'engine-'.$client->id,
            'monthly_price' => 99,
            'currency_code' => 'EUR',
            'workflow_limit' => 10,
            'records_per_month' => $records,
            'ai_budget' => 10,
            'is_active' => true,
        ]);
        ClientSubscription::query()->create([
            'user_id' => $client->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
        ]);
    }
}
