<?php

namespace App\Livewire\Workspace;

use App\Events\KanbanBoardUpdated;
use App\Models\KanbanBoard;
use App\Models\KanbanColumn;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class KanbanView extends Component
{
    public KanbanBoard $board;

    public ?int $selectedTaskId = null;

    /* ===== Column management ===== */
    public string $newColumnName = '';
    public bool $showAddColumn = false;
    public ?int $editingColumnId = null;
    public string $editingColumnName = '';

    /* ===== Card creation ===== */
    public ?int $addingCardToColumn = null;
    public string $newCardTitle = '';

    /* ===== Sharing ===== */
    public bool $showSharePanel = false;
    public string $inviteEmail = '';
    public ?string $inviteError = null;
    public ?string $inviteSuccess = null;

    public function mount(KanbanBoard $board): void
    {
        abort_if(! $board->canAccess(Auth::id()), 403);
    }

    /** Re-verify access on every write — catches mid-session revocations. */
    private function guard(): void
    {
        abort_if(! $this->board->canAccess(Auth::id()), 403);
    }

    private function broadcastUpdate(?int $removedUserId = null): void
    {
        try {
            event(new KanbanBoardUpdated($this->board->id, Auth::id(), $removedUserId));
        } catch (\Throwable) {
            // Broadcasting unavailable — real-time sync skipped, core action still succeeds
        }
    }

    private function isOwner(): bool
    {
        return $this->board->user_id === Auth::id();
    }

    /** Find a task that belongs to any column of this board. */
    private function findBoardTask(int $taskId): ?Task
    {
        $columnIds = $this->board->columns()->pluck('id');
        return Task::whereIn('kanban_column_id', $columnIds)->find($taskId);
    }

    /* ============================================================ *
     *  Columns
     * ============================================================ */

    public function addColumn(): void
    {
        $this->guard();
        $this->validate(['newColumnName' => 'required|string|max:100']);

        $position = $this->board->columns()->max('position') + 1;

        $this->board->columns()->create([
            'name'     => trim($this->newColumnName),
            'position' => $position,
        ]);

        $this->newColumnName  = '';
        $this->showAddColumn  = false;

        $this->dispatch('kanban-refresh-sortable');
        $this->broadcastUpdate();
    }

    public function startRenameColumn(int $columnId): void
    {
        $col = $this->board->columns()->findOrFail($columnId);
        $this->editingColumnId   = $columnId;
        $this->editingColumnName = $col->name;
    }

    public function saveColumnName(): void
    {
        $this->guard();
        if (! $this->editingColumnId) {
            return;
        }

        $this->validate(['editingColumnName' => 'required|string|max:100']);

        $this->board->columns()
            ->where('id', $this->editingColumnId)
            ->update(['name' => trim($this->editingColumnName)]);

        $this->editingColumnId   = null;
        $this->editingColumnName = '';

        $this->broadcastUpdate();
    }

    public function cancelColumnRename(): void
    {
        $this->editingColumnId   = null;
        $this->editingColumnName = '';
    }

    public function deleteColumn(int $columnId): void
    {
        $this->guard();
        $col = $this->board->columns()->findOrFail($columnId);

        Task::where('kanban_column_id', $columnId)
            ->update(['kanban_column_id' => null, 'kanban_position' => 0]);

        $col->delete();

        $this->dispatch('kanban-refresh-sortable');
        $this->dispatch('toast', type: 'info', title: 'Колонка удалена, задачи возвращены во Входящие');
        $this->broadcastUpdate();
    }

    public function setColumnColor(int $columnId, string $color): void
    {
        $this->guard();
        $this->board->columns()->where('id', $columnId)
            ->update(['color' => $color === '' ? null : $color]);

        $this->broadcastUpdate();
    }

    public function reorderColumns(array $orderedIds): void
    {
        $this->guard();
        $boardColumnIds = $this->board->columns()->pluck('id')->flip();

        foreach ($orderedIds as $position => $id) {
            if ($boardColumnIds->has((int) $id)) {
                KanbanColumn::where('id', (int) $id)->update(['position' => $position]);
            }
        }

        $this->broadcastUpdate();
    }

    /* ============================================================ *
     *  Cards
     * ============================================================ */

    public function startAddCard(int $columnId): void
    {
        $this->addingCardToColumn = $columnId;
        $this->newCardTitle       = '';
    }

    public function addCard(): void
    {
        $this->guard();
        if (! $this->addingCardToColumn) {
            return;
        }

        $this->validate(['newCardTitle' => 'required|string|max:500']);

        $this->board->columns()->findOrFail($this->addingCardToColumn);

        $position = Task::where('kanban_column_id', $this->addingCardToColumn)->max('kanban_position') + 1;

        Task::create([
            'user_id'         => Auth::id(),
            'kanban_column_id' => $this->addingCardToColumn,
            'title'           => trim($this->newCardTitle),
            'kanban_position' => $position,
        ]);

        $this->newCardTitle       = '';
        $this->addingCardToColumn = null;

        $this->broadcastUpdate();
    }

    public function cancelAddCard(): void
    {
        $this->addingCardToColumn = null;
        $this->newCardTitle       = '';
    }

    public function moveCard(int $taskId, int $toColumnId, array $orderedIds): void
    {
        $this->guard();
        if (! $this->board->columns()->where('id', $toColumnId)->exists()) {
            return;
        }

        $task = $this->findBoardTask($taskId);

        if (! $task) {
            return;
        }

        $task->update(['kanban_column_id' => $toColumnId]);

        $boardColumnIds = $this->board->columns()->pluck('id');
        foreach ($orderedIds as $position => $id) {
            Task::whereIn('kanban_column_id', $boardColumnIds)
                ->where('id', (int) $id)
                ->update(['kanban_position' => $position]);
        }

        $this->broadcastUpdate();
    }

    /* ============================================================ *
     *  Task detail
     * ============================================================ */

    public function selectTask(int $taskId): void
    {
        $this->selectedTaskId = $taskId;
    }

    public function closeTask(): void
    {
        $this->selectedTaskId = null;
    }

    public function clearSelection(): void
    {
        $this->selectedTaskId = null;
    }

    public function toggleCompleted(int $taskId): void
    {
        $this->guard();
        $task = $this->findBoardTask($taskId);
        if (! $task) return;
        $task->update(['completed_at' => $task->completed_at ? null : now()]);

        if ($this->selectedTaskId === $taskId) {
            $this->selectedTaskId = null;
        }

        $this->broadcastUpdate();
    }

    /* ============================================================ *
     *  Sharing
     * ============================================================ */

    public function toggleSharePanel(): void
    {
        $this->showSharePanel = ! $this->showSharePanel;
        $this->inviteEmail    = '';
        $this->inviteError    = null;
        $this->inviteSuccess  = null;
    }

    public function inviteMember(): void
    {
        if (! $this->isOwner()) {
            return;
        }

        $this->inviteError   = null;
        $this->inviteSuccess = null;

        $this->validate(['inviteEmail' => 'required|email']);

        $user = User::where('email', trim($this->inviteEmail))->first();

        if (! $user) {
            $this->inviteError = 'Пользователь с таким email не найден.';
            return;
        }

        if ($user->id === Auth::id()) {
            $this->inviteError = 'Нельзя добавить себя.';
            return;
        }

        if ($this->board->members()->where('user_id', $user->id)->exists()) {
            $this->inviteError = 'Этот пользователь уже имеет доступ.';
            return;
        }

        $this->board->members()->attach($user->id);

        $this->inviteEmail   = '';
        $this->inviteSuccess = "«{$user->name}» добавлен в доску.";
    }

    public function removeMember(int $userId): void
    {
        if (! $this->isOwner()) {
            return;
        }

        // Broadcast BEFORE detach so the removed user is still authorised on the channel
        // and receives the kick event immediately.
        $this->broadcastUpdate(removedUserId: $userId);

        $this->board->members()->detach($userId);
        $this->inviteSuccess = null;
        $this->inviteError   = null;
    }

    /* ============================================================ *
     *  Render
     * ============================================================ */

    public function render()
    {
        $board = KanbanBoard::with([
            'columns' => fn ($q) => $q->orderBy('position'),
            'columns.tasks' => fn ($q) => $q->whereNull('deleted_at')->orderBy('kanban_position')->with('tags'),
            'members:id,name,email',
        ])->findOrFail($this->board->id);

        $selectedTask = $this->selectedTaskId
            ? Task::forUser(Auth::id())->with(['tags', 'checklist', 'attachments', 'project'])->find($this->selectedTaskId)
            : null;

        return view('livewire.workspace.kanban-view', [
            'board'        => $board,
            'selectedTask' => $selectedTask,
            'isOwner'      => $this->isOwner(),
        ])->title($board->name . ' · Доска');
    }
}
