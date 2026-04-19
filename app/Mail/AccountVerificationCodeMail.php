<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountVerificationCodeMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public User $user,
        public string $code,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Код за потвърждение на профила',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-verification-code',
        );
    }
}
