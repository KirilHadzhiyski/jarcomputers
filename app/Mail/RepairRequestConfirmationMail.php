<?php

namespace App\Mail;

use App\Models\RepairRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RepairRequestConfirmationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public RepairRequest $repairRequest,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Потвърждение за заявка за ремонт',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.repair-request-confirmation',
        );
    }
}
