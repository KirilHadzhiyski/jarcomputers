<?php

namespace App\Services\Communications;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\RepairRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConversationService
{
    public function createFromRepairRequest(RepairRequest $repairRequest): Conversation
    {
        $conversation = Conversation::query()->firstOrCreate(
            [
                'repair_request_id' => $repairRequest->id,
                'channel' => 'website-form',
            ],
            [
                'customer_name' => $repairRequest->name,
                'customer_phone' => $repairRequest->phone,
                'customer_email' => $repairRequest->email,
                'status' => 'open',
                'last_message_at' => $repairRequest->created_at ?? now(),
                'meta' => [
                    'preferred_contact' => $repairRequest->preferred_contact,
                    'source_page' => $repairRequest->source_page,
                ],
            ],
        );

        if (! $conversation->messages()->exists()) {
            $conversation->messages()->create([
                'repair_request_id' => $repairRequest->id,
                'channel' => 'website-form',
                'direction' => 'inbound',
                'status' => 'received',
                'sender_name' => $repairRequest->name,
                'sender_handle' => $repairRequest->phone ?: $repairRequest->email,
                'content' => $repairRequest->issue,
                'payload' => [
                    'city' => $repairRequest->city,
                    'model' => $repairRequest->model,
                    'preferred_contact' => $repairRequest->preferred_contact,
                    'source_page' => $repairRequest->source_page,
                ],
                'received_at' => $repairRequest->created_at ?? now(),
            ]);
        }

        return $conversation;
    }

    public function storeInboundMessage(string $channel, array $normalized, array $payload): ConversationMessage
    {
        $key = hash('sha256', $channel.':'.($normalized['external_conversation_id'] ?? ''));

        return Cache::lock('inbound:'.$key, 30)->block(10,
            fn () => DB::transaction(fn () => $this->persistInboundMessage($channel, $normalized, $payload)));
    }

    private function persistInboundMessage(string $channel, array $normalized, array $payload): ConversationMessage
    {
        $providerId = $normalized['provider_message_id'] ?? null;
        if (filled($providerId)) {
            $existing = ConversationMessage::where('channel', $channel)->where('provider_message_id', $providerId)->first();
            if ($existing) {
                return $existing;
            }
        }
        $repairRequest = $this->findMatchingRepairRequest($normalized);
        $timestamp = $normalized['received_at'] ?? now();

        $conversation = Conversation::query()->firstOrCreate(
            [
                'channel' => $channel,
                'external_conversation_id' => $normalized['external_conversation_id'],
            ],
            [
                'repair_request_id' => $repairRequest?->id,
                'customer_name' => $normalized['customer_name'] ?? null,
                'customer_phone' => $normalized['customer_phone'] ?? null,
                'customer_email' => $normalized['customer_email'] ?? null,
                'external_user_id' => $normalized['external_user_id'] ?? null,
                'status' => 'open',
                'last_message_at' => $timestamp,
                'meta' => $normalized['meta'] ?? null,
            ],
        );

        $conversation->forceFill([
            'repair_request_id' => $conversation->repair_request_id ?: $repairRequest?->id,
            'customer_name' => ($normalized['customer_name'] ?? null) ?: $conversation->customer_name,
            'customer_phone' => ($normalized['customer_phone'] ?? null) ?: $conversation->customer_phone,
            'customer_email' => ($normalized['customer_email'] ?? null) ?: $conversation->customer_email,
            'external_user_id' => ($normalized['external_user_id'] ?? null) ?: $conversation->external_user_id,
            'last_message_at' => $conversation->last_message_at && $conversation->last_message_at->gt($timestamp) ? $conversation->last_message_at : $timestamp,
            'status' => 'open',
            'meta' => array_filter(array_merge($conversation->meta ?? [], $normalized['meta'] ?? [])),
        ])->save();

        return $conversation->messages()->firstOrCreate([
            'dedupe_key' => filled($providerId) ? hash('sha256', $channel.':'.$providerId) : (string) Str::uuid(),
        ], [
            'repair_request_id' => $conversation->repair_request_id,
            'channel' => $channel,
            'direction' => 'inbound',
            'status' => 'received',
            'provider_message_id' => $normalized['provider_message_id'] ?? null,
            'sender_name' => $normalized['customer_name'] ?? null,
            'sender_handle' => $normalized['sender_handle'] ?? null,
            'content' => $normalized['content'] ?? null,
            'payload' => $payload,
            'received_at' => $timestamp,
        ]);
    }

    private function findMatchingRepairRequest(array $normalized): ?RepairRequest
    {
        $phone = $normalized['customer_phone'] ?? null;
        $email = $normalized['customer_email'] ?? null;

        if (blank($phone) && blank($email)) {
            return null;
        }

        return RepairRequest::query()
            ->where(function ($query) use ($phone, $email) {
                if (filled($phone)) {
                    $query->where('phone', $phone);
                }

                if (filled($email)) {
                    $query->orWhere('email', $email);
                }
            })
            ->latest()
            ->first();
    }
}
