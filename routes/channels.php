<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('workspace.{workspaceId}', function ($user, $workspaceId) {
    return \App\Models\WorkspaceMember::where('workspace_id', $workspaceId)
        ->where('user_id', $user->id)
        ->exists();
});

Broadcast::channel('kanban.board.{boardId}', function ($user, $boardId) {
    $board = \App\Models\KanbanBoard::find($boardId);
    return $board && $board->canAccess($user->id);
});
