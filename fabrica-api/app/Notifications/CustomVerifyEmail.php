<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmail extends Notification
{
    protected $senha;

    // Recebe a senha no construtor (se necessário)
    public function __construct($senha = null)
    {
        $this->senha = $senha;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // Gera o link de verificação
        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification())
            ]
        );

        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->view(
                'emails.verificacao-personalizada',
                [
                    'url' => $verifyUrl,
                    'user' => $notifiable
                ]
            )
            ->subject('Confirme seu endereço de e-mail');
    }
}
