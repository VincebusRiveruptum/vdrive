<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationCodeNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected string $code
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Código de verificación - vdrive')
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('Tu código de verificación es:')
            ->line('**' . $this->code . '**')
            ->line('Este código expira en 15 minutos.')
            ->line('Si no solicitaste este código, puedes ignorar este email.')
            ->salutation('Saludos, vdrive');
    }
}
