<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $code,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Код подтверждения Tomodoro: ' . $this->code);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verify-code',
            with: [
                'name'  => $this->user->name,
                'email' => $this->user->email,
                'code'  => $this->code,
            ],
        );
    }
}
