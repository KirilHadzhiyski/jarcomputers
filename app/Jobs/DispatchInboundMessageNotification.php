<?php

namespace App\Jobs;

use App\Models\ConversationMessage;
use App\Services\Communications\LeadNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchInboundMessageNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $conversationMessageId,
    ) {
    }

    public function handle(LeadNotificationService $leadNotificationService): void
    {
        $conversationMessage = ConversationMessage::query()
            ->with(['conversation', 'repairRequest'])
            ->find($this->conversationMessageId);

        if (! $conversationMessage) {
            return;
        }

        $leadNotificationService->notifyInboundMessage($conversationMessage);
    }
}
