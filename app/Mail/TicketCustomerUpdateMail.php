<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Models\TicketUpdate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketCustomerUpdateMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public TicketUpdate $update,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Обновление по поръчка #{$this->ticket->id}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-customer-update',
        );
    }
}
