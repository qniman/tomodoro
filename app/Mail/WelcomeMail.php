<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Добро пожаловать в Tomodoro 🍅');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
            with: [
                'name'     => $this->user->name,
                'email'    => $this->user->email,
                'verified' => (bool) $this->user->email_verified_at,
            ],
        );
    }
}
