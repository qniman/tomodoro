<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $token,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Сброс пароля Tomodoro');
    }

    public function content(): Content
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $this->user->email,
        ], false));

        return new Content(
            view: 'emails.password-reset',
            with: [
                'name'     => $this->user->name,
                'email'    => $this->user->email,
                'resetUrl' => $resetUrl,
            ],
        );
    }
}
