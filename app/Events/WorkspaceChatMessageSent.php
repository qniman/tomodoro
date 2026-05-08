<?php

namespace App\Events;

use App\Models\WorkspaceMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkspaceChatMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly WorkspaceMessage $message,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("workspace.{$this->message->workspace_id}")];
    }

    public function broadcastAs(): string
    {
        return 'chat.message';
    }

    public function broadcastWith(): array
    {
        return [
            'id'         => $this->message->id,
            'body'       => $this->message->body,
            'user_id'    => $this->message->user_id,
            'user_name'  => $this->message->user->name,
            'user_avatar'=> $this->message->user->avatar_url ?? null,
            'created_at' => $this->message->created_at->toIso8601String(),
        ];
    }
}
