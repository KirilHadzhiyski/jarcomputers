<?php

namespace Tests\Feature;

use App\Jobs\DispatchInboundMessageNotification;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class MessagingWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_whatsapp_webhook_verification_returns_the_challenge(): void
    {
        config([
            'communications.channels.whatsapp.verify_token' => 'test-token',
        ]);

        $this->get('/webhooks/whatsapp?hub.mode=subscribe&hub.verify_token=test-token&hub.challenge=123456')
            ->assertOk()
            ->assertSee('123456');
    }

    public function test_viber_message_is_stored_as_conversation_message(): void
    {
        Bus::fake();

        $response = $this->postJson(route('webhooks.receive', ['channel' => 'viber']), [
            'event' => 'message',
            'timestamp' => 1_776_200_303_692,
            'message_token' => 998877,
            'sender' => [
                'id' => 'viber-user-1',
                'name' => 'Тест Клиент',
            ],
            'message' => [
                'text' => 'Здравейте, искам да попитам за ремонт.',
            ],
        ]);

        $response->assertOk()
            ->assertJson([
                'received' => 1,
            ]);

        $conversation = Conversation::query()->first();
        $message = ConversationMessage::query()->first();

        $this->assertNotNull($conversation);
        $this->assertNotNull($message);

        $this->assertDatabaseHas(Conversation::class, [
            'id' => $conversation->id,
            'channel' => 'viber',
            'external_conversation_id' => 'viber-user-1',
            'customer_name' => 'Тест Клиент',
        ]);

        $this->assertDatabaseHas(ConversationMessage::class, [
            'id' => $message->id,
            'conversation_id' => $conversation->id,
            'channel' => 'viber',
            'direction' => 'inbound',
            'content' => 'Здравейте, искам да попитам за ремонт.',
        ]);

        Bus::assertDispatched(DispatchInboundMessageNotification::class);
    }
}
