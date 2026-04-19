<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expireMinutes = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);
        $brand = (string) config('site.brand', config('app.name'));
        $supportEmail = (string) config('site.support_email', config('mail.from.address'));

        return (new MailMessage)
            ->subject("Смяна на парола | {$brand}")
            ->greeting('Здравейте'.(filled($notifiable->name) ? ", {$notifiable->name}" : '').'!')
            ->line('Получихме заявка за смяна на паролата за вашия профил.')
            ->action('Смени паролата', $this->resetUrl($notifiable))
            ->line("Линкът е валиден {$expireMinutes} минути.")
            ->line("Ако не сте поискали тази промяна, просто игнорирайте това съобщение или се свържете с нас на {$supportEmail}.");
    }

    private function resetUrl(object $notifiable): string
    {
        return route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }
}
