<?php

namespace App\Mail;

use App\Models\ConversationMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InboundMessageAlertMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public ConversationMessage $conversationMessage,
    ) {
    }

    public function envelope(): Envelope
    {
        $sender = $this->conversationMessage->sender_name ?: $this->conversationMessage->sender_handle ?: 'клиент';

        return new Envelope(
            subject: "Ново съобщение от {$sender} ({$this->conversationMessage->channel})",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.inbound-message-alert',
        );
    }
}
