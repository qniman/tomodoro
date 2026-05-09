<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskDeadlineMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @param array{overdue: array, today: array, tomorrow: array} $tasks */
    public function __construct(
        public readonly User $user,
        public readonly array $tasks,
    ) {}

    public function envelope(): Envelope
    {
        $overdue = count($this->tasks['overdue'] ?? []);
        $today   = count($this->tasks['today'] ?? []);

        $subject = $overdue > 0
            ? "🔥 {$overdue} просроченных задач — Tomodoro"
            : "⏰ {$today} задач на сегодня — Tomodoro";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.task-deadline',
            with: [
                'name'          => $this->user->name,
                'tasks'         => $this->tasks,
                'overdueCount'  => count($this->tasks['overdue'] ?? []),
                'todayCount'    => count($this->tasks['today'] ?? []),
                'tomorrowCount' => count($this->tasks['tomorrow'] ?? []),
            ],
        );
    }
}
