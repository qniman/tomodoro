<?php

namespace App\Events;

use App\Models\WorkspaceMember;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkspaceMemberStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly WorkspaceMember $member,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("workspace.{$this->member->workspace_id}")];
    }

    public function broadcastAs(): string
    {
        return 'member.status';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id'        => $this->member->user_id,
            'status'         => $this->member->status,
            'pomodoros_today'=> $this->member->pomodoros_today,
        ];
    }
}
