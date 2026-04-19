<?php

namespace App\Mail;

use App\Models\RepairRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RepairRequestAdminMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public RepairRequest $repairRequest,
    ) {
    }

    public function envelope(): Envelope
    {
        $model = $this->repairRequest->model ? " - {$this->repairRequest->model}" : '';

        return new Envelope(
            subject: "Нова заявка за ремонт{$model}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.repair-request-admin',
        );
    }
}
