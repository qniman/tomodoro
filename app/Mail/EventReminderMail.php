<?php

namespace App\Mail;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly array $event,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📅 Напоминание: ' . $this->event['title'],
        );
    }

    public function content(): Content
    {
        $startsAt    = Carbon::parse($this->event['starts_at']);
        $diffMinutes = (int) now()->diffInMinutes($startsAt, false);

        if ($diffMinutes <= 0) {
            $startsInText = 'начинается сейчас';
        } elseif ($diffMinutes < 60) {
            $startsInText = "через {$diffMinutes} мин";
        } elseif ($diffMinutes < 1440) {
            $hours = (int) round($diffMinutes / 60);
            $startsInText = "через {$hours} ч";
        } else {
            $days = (int) round($diffMinutes / 1440);
            $startsInText = "через {$days} д";
        }

        return new Content(
            view: 'emails.event-reminder',
            with: [
                'name'         => $this->user->name,
                'event'        => $this->event,
                'startsInText' => $startsInText,
            ],
        );
    }
}
