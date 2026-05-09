<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailChangeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $newEmail,
        public readonly string $code,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: $this->newEmail,
            subject: 'Код подтверждения нового email Tomodoro: ' . $this->code,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.email-change',
            with: [
                'name'     => $this->user->name,
                'newEmail' => $this->newEmail,
                'code'     => $this->code,
            ],
        );
    }
}
