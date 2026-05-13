<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KanbanBoardUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $boardId,
        public readonly int $triggeredBy,
        public readonly ?int $removedUserId = null,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("kanban.board.{$this->boardId}")];
    }

    public function broadcastAs(): string
    {
        return 'board.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'triggeredBy'   => $this->triggeredBy,
            'removedUserId' => $this->removedUserId,
        ];
    }
}
