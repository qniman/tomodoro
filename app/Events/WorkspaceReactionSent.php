<?php

namespace App\Events;

use App\Models\WorkspaceReaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkspaceReactionSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly WorkspaceReaction $reaction,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("workspace.{$this->reaction->workspace_id}")];
    }

    public function broadcastAs(): string
    {
        return 'reaction.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id'           => $this->reaction->id,
            'emoji'        => $this->reaction->emoji,
            'from_user_id' => $this->reaction->from_user_id,
            'from_name'    => $this->reaction->fromUser->name,
            'to_user_id'   => $this->reaction->to_user_id,
        ];
    }
}
