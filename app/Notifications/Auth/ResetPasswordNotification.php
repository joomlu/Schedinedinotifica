<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expire = (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

        return (new MailMessage)
            ->subject('Recupero password - Schedine di Notifica')
            ->greeting('Ciao ' . ($notifiable->display_name ?? $notifiable->name ?? ''))
            ->line('Abbiamo ricevuto una richiesta di recupero password per il tuo accesso a Schedine di Notifica.')
            ->action('Imposta una nuova password', $this->resetUrl($notifiable))
            ->line('Questo link scadrà tra ' . $expire . ' minuti.')
            ->line('Se non hai richiesto tu il recupero password, puoi ignorare questa email in sicurezza.');
    }

    protected function resetUrl(object $notifiable): string
    {
        $path = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false);

        return rtrim(config('app.url'), '/') . $path;
    }
}
