<?php

namespace App\Services\Communications;

use App\Jobs\DispatchInboundMessageNotification;
use App\Models\ConversationMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InboundMessageService
{
    public function __construct(
        private readonly ConversationService $conversationService,
        private readonly WebhookSignatureValidator $signatureValidator,
    ) {
    }

    public function verify(string $channel, Request $request): Response
    {
        $this->assertSupportedChannel($channel);

        if (! in_array($channel, ['facebook-messenger', 'whatsapp'], true)) {
            return response('ok', 200);
        }

        $verifyToken = (string) config("communications.channels.{$channel}.verify_token");
        $mode = (string) $request->query('hub_mode', $request->query('hub.mode'));
        $token = (string) $request->query('hub_verify_token', $request->query('hub.verify_token'));
        $challenge = (string) $request->query('hub_challenge', $request->query('hub.challenge'));

        if ($mode === 'subscribe' && $verifyToken !== '' && hash_equals($verifyToken, $token)) {
            return response($challenge, 200);
        }

        throw new AccessDeniedHttpException('Webhook verification failed.');
    }

    /**
     * @return Collection<int, ConversationMessage>
     */
    public function receive(string $channel, Request $request): Collection
    {
        $this->assertSupportedChannel($channel);
        $this->signatureValidator->validate($channel, $request);

        $payload = $request->json()->all() ?: $request->all();
        $messages = collect($this->normalize($channel, $payload))
            ->filter(fn (array $message) => filled($message['external_conversation_id'] ?? null));

        return $messages->map(function (array $message) use ($channel, $payload) {
            $stored = $this->conversationService->storeInboundMessage($channel, $message, $payload);
            DispatchInboundMessageNotification::dispatch($stored->id);

            return $stored;
        });
    }

    private function normalize(string $channel, array $payload): array
    {
        return match ($channel) {
            'whatsapp' => $this->normalizeWhatsApp($payload),
            'facebook-messenger' => $this->normalizeFacebookMessenger($payload),
            'viber' => $this->normalizeViber($payload),
            default => [],
        };
    }

    private function normalizeWhatsApp(array $payload): array
    {
        $contacts = collect(data_get($payload, 'entry.0.changes.0.value.contacts', []))
            ->keyBy('wa_id');

        return collect(data_get($payload, 'entry.0.changes.0.value.messages', []))
            ->map(function (array $message) use ($contacts) {
                $from = data_get($message, 'from');
                $contact = $contacts->get($from, []);

                return [
                    'external_conversation_id' => $from,
                    'external_user_id' => $from,
                    'customer_name' => data_get($contact, 'profile.name'),
                    'customer_phone' => $from,
                    'customer_email' => null,
                    'sender_handle' => $from,
                    'provider_message_id' => data_get($message, 'id'),
                    'content' => data_get($message, 'text.body')
                        ?? data_get($message, 'button.text')
                        ?? data_get($message, 'interactive.button_reply.title')
                        ?? data_get($message, 'interactive.list_reply.title'),
                    'received_at' => filled(data_get($message, 'timestamp'))
                        ? Carbon::createFromTimestampUTC((int) data_get($message, 'timestamp'))
                        : now(),
                    'meta' => [
                        'type' => data_get($message, 'type'),
                    ],
                ];
            })
            ->all();
    }

    private function normalizeFacebookMessenger(array $payload): array
    {
        return collect(data_get($payload, 'entry', []))
            ->flatMap(fn (array $entry) => $entry['messaging'] ?? [])
            ->filter(fn (array $event) => filled(data_get($event, 'message.text')))
            ->map(function (array $event) {
                return [
                    'external_conversation_id' => data_get($event, 'sender.id'),
                    'external_user_id' => data_get($event, 'sender.id'),
                    'customer_name' => null,
                    'customer_phone' => null,
                    'customer_email' => null,
                    'sender_handle' => data_get($event, 'sender.id'),
                    'provider_message_id' => data_get($event, 'message.mid'),
                    'content' => data_get($event, 'message.text'),
                    'received_at' => filled(data_get($event, 'timestamp'))
                        ? Carbon::createFromTimestampUTC((int) floor(data_get($event, 'timestamp') / 1000))
                        : now(),
                    'meta' => [
                        'recipient_id' => data_get($event, 'recipient.id'),
                    ],
                ];
            })
            ->all();
    }

    private function normalizeViber(array $payload): array
    {
        $senderId = data_get($payload, 'sender.id');
        $content = data_get($payload, 'message.text')
            ?? data_get($payload, 'message.media');

        if (blank($senderId) || blank($content)) {
            return [];
        }

        return [[
            'external_conversation_id' => $senderId ?: (string) data_get($payload, 'chat_hostname', Str::uuid()->toString()),
            'external_user_id' => $senderId,
            'customer_name' => data_get($payload, 'sender.name'),
            'customer_phone' => null,
            'customer_email' => null,
            'sender_handle' => $senderId,
            'provider_message_id' => (string) (data_get($payload, 'message_token') ?? data_get($payload, 'message.id') ?? Str::uuid()),
            'content' => $content,
            'received_at' => filled(data_get($payload, 'timestamp'))
                ? Carbon::createFromTimestampUTC((int) floor(data_get($payload, 'timestamp') / 1000))
                : now(),
            'meta' => [
                'event' => data_get($payload, 'event'),
            ],
        ]];
    }

    private function assertSupportedChannel(string $channel): void
    {
        if (! in_array($channel, ['whatsapp', 'facebook-messenger', 'viber'], true)) {
            throw new NotFoundHttpException();
        }
    }
}
