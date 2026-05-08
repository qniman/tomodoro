<?php

namespace App\Events;

use App\Models\WorkspaceSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkspaceTimerUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $workspaceId,
        public readonly ?WorkspaceSession $session,
        public readonly string $action, // started | paused | resumed | stopped | finished
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("workspace.{$this->workspaceId}")];
    }

    public function broadcastAs(): string
    {
        return 'timer.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'action'  => $this->action,
            'session' => $this->session ? [
                'id'               => $this->session->id,
                'phase'            => $this->session->phase,
                'duration_seconds' => $this->session->duration_seconds,
                'started_at_ms'    => $this->session->started_at->getTimestampMs(),
                'paused_at_ms'     => $this->session->paused_at?->getTimestampMs(),
                'started_by_name'  => $this->session->startedBy->name,
                'remaining'        => $this->session->remainingSeconds(),
            ] : null,
        ];
    }
}
