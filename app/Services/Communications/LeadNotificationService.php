<?php

namespace App\Services\Communications;

use App\Mail\InboundMessageAlertMail;
use App\Mail\RepairRequestAdminMail;
use App\Mail\RepairRequestConfirmationMail;
use App\Models\ConversationMessage;
use App\Models\NotificationDelivery;
use App\Models\RepairRequest;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class LeadNotificationService
{
    public function notifyNewRepairRequest(RepairRequest $repairRequest): void
    {
        $repairRequest->loadMissing('conversations');
        $conversation = $repairRequest->conversations()->latest('created_at')->first();

        foreach (config('communications.email.notifications_to', []) as $target) {
            $this->deliverEmail(
                repairRequest: $repairRequest,
                conversationId: $conversation?->id,
                target: $target,
                subject: 'Нова заявка за ремонт',
                mailable: new RepairRequestAdminMail($repairRequest),
            );
        }

        if ($repairRequest->email) {
            $this->deliverEmail(
                repairRequest: $repairRequest,
                conversationId: $conversation?->id,
                target: $repairRequest->email,
                subject: 'Потвърждение за заявка за ремонт',
                mailable: new RepairRequestConfirmationMail($repairRequest),
            );
        }
    }

    public function notifyInboundMessage(ConversationMessage $conversationMessage): void
    {
        $conversationMessage->loadMissing(['conversation', 'repairRequest']);

        foreach (config('communications.email.notifications_to', []) as $target) {
            $this->deliverEmail(
                repairRequest: $conversationMessage->repairRequest,
                conversationId: $conversationMessage->conversation_id,
                target: $target,
                subject: 'Ново входящо съобщение',
                mailable: new InboundMessageAlertMail($conversationMessage),
            );
        }
    }

    private function deliverEmail(
        ?RepairRequest $repairRequest,
        ?string $conversationId,
        string $target,
        string $subject,
        Mailable $mailable,
    ): void {
        $delivery = NotificationDelivery::query()->create([
            'repair_request_id' => $repairRequest?->id,
            'conversation_id' => $conversationId,
            'channel' => 'email',
            'target' => $target,
            'subject' => $subject,
            'status' => 'pending',
        ]);

        try {
            Mail::to($target)->send($mailable);

            $delivery->forceFill([
                'status' => 'sent',
                'response_code' => '202',
                'response_body' => 'Delivered via Laravel mailer.',
                'delivered_at' => now(),
            ])->save();
        } catch (Throwable $exception) {
            report($exception);

            $delivery->forceFill([
                'status' => 'failed',
                'response_code' => '500',
                'response_body' => mb_substr($exception->getMessage(), 0, 65535),
            ])->save();
        }
    }
}
