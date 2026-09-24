<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\RepairRequest;
use App\Models\User;
use App\Services\Business\BusinessIntakeService;
use App\Services\Communications\ConversationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class AdminWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_original_admin_shell_requires_an_admin_and_data_is_not_public(): void
    {
        $this->getJson('/admin/workspace/data')->assertUnauthorized();
        $this->actingAs(User::factory()->create(['role' => 'user']))->getJson('/admin/workspace/data')->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/admin')->assertOk()->assertSee('Админ табло');
        $this->getJson('/admin/workspace/data')->assertOk()->assertJsonCount(0, 'opportunities');
    }

    public function test_website_request_automatically_becomes_customer_order_and_conversation_once(): void
    {
        Bus::fake();
        $this->postJson('/repair-requests', [
            'name' => 'Test Customer', 'phone' => '0888123456', 'email' => 'customer@example.com',
            'model' => 'iPhone 13', 'issue' => 'Broken display', 'gdpr_consent' => true, 'preferred_contact' => 'email',
        ])->assertCreated();
        $repair = RepairRequest::firstOrFail();
        app(BusinessIntakeService::class)->repairRequest($repair);
        $this->assertDatabaseCount('business_orders', 1);
        $this->assertDatabaseCount('customer_profiles', 1);
        $this->assertDatabaseCount('conversations', 1);
        $this->assertDatabaseHas('customer_profiles', ['email' => 'customer@example.com', 'phone' => '359888123456']);
        $this->actingAs(User::factory()->create(['role' => 'admin']))->patchJson('/admin/workspace/requests/'.$repair->id, ['status' => 'completed'])->assertOk();
        $this->assertDatabaseHas('business_orders', ['repair_request_id' => $repair->id, 'status' => 'ready']);
        $this->patchJson('/admin/workspace/requests/'.$repair->id, ['status' => 'invented'])->assertUnprocessable();
        $this->getJson('/admin/workspace/data')->assertJsonPath('statusCounts.completed', 1);
    }

    public function test_read_conversation_becomes_unread_only_for_a_newer_message(): void
    {
        $this->freezeTime();
        $normalized = ['external_conversation_id' => 'thread', 'provider_message_id' => 'one', 'customer_email' => 'customer@example.com', 'content' => '<script>bad()</script>', 'received_at' => now()];
        $service = app(ConversationService::class);
        $message = $service->storeInboundMessage('email', $normalized, []);
        $this->actingAs(User::factory()->create(['role' => 'admin']))->patchJson('/admin/workspace/inquiries/'.$message->conversation_id.'/read')->assertOk();
        $service->storeInboundMessage('email', $normalized, []);
        $this->getJson('/admin/workspace/data')->assertJsonPath('unreadCount', 0);
        $this->travel(1)->minute();
        $service->storeInboundMessage('email', [...$normalized, 'provider_message_id' => 'two', 'received_at' => now()], []);
        $this->getJson('/admin/workspace/data')->assertJsonPath('unreadCount', 1);
        $service->storeInboundMessage('email', [...$normalized, 'provider_message_id' => 'old', 'received_at' => now()->subDay()], []);
        $this->assertSame(now()->toDateTimeString(), Conversation::first()->last_message_at->toDateTimeString());
        $this->get('/admin/inbox/'.$message->conversation_id)->assertOk()->assertDontSee('<script>bad()</script>', false);
        $this->assertDatabaseCount('conversation_messages', 3);
        $this->assertDatabaseCount('customer_profiles', 1);
    }

    public function test_analysis_configuration_is_persisted_updated_and_archived_on_server(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $data = [
            'name' => 'Custom PC', 'itemType' => 'configuration', 'targetMarket' => 'greece', 'buyCost' => 500,
            'localSalePrice' => 650, 'targetSalePrice' => 800, 'competitorPrice' => 850,
            'marketBenchmarkPrices' => ['greece' => 850, 'czechia' => 860, 'romania' => 840],
            'shippingCost' => 20, 'marketplaceFeePercent' => 8, 'minMarginPercent' => 12,
            'demandScore' => 5, 'competitionScore' => 6, 'notes' => 'Test assumptions',
        ];
        $id = $this->postJson('/admin/workspace/opportunities', $data)->assertOk()->json('id');
        $this->assertDatabaseHas('pricing_configurations', ['id' => $id, 'name' => 'Custom PC', 'status' => 'draft', 'base_price_bgn' => 977.92]);
        $this->getJson('/admin/workspace/data')->assertJsonPath('opportunities.0.buyCost', 500);
        $this->putJson('/admin/workspace/opportunities/'.$id, [...$data, 'buyCost' => 520])->assertOk();
        $this->assertDatabaseCount('pricing_configurations', 1);
        $this->deleteJson('/admin/workspace/opportunities/'.$id)->assertOk();
        $this->assertDatabaseHas('pricing_configurations', ['id' => $id, 'status' => 'archived']);
        $this->getJson('/admin/workspace/data')->assertJsonCount(0, 'opportunities');
    }
}
