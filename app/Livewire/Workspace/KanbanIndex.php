<?php

namespace App\Livewire\Workspace;

use App\Models\KanbanBoard;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Канбан-доски')]
class KanbanIndex extends Component
{
    public string $newBoardName = '';
    public string $newBoardColor = '#6366f1';
    public bool $showCreateForm = false;

    public ?int $renamingBoardId = null;
    public string $renamingBoardName = '';

    private function boardCountQuery()
    {
        return fn ($q) => $q->join('tasks', 'kanban_columns.id', '=', 'tasks.kanban_column_id')
            ->whereNull('tasks.deleted_at');
    }

    public function createBoard(): void
    {
        $this->validate([
            'newBoardName'  => 'required|string|max:100',
            'newBoardColor' => 'required|string|max:32',
        ]);

        $position = KanbanBoard::where('user_id', Auth::id())->max('position') + 1;

        $board = KanbanBoard::create([
            'user_id'  => Auth::id(),
            'name'     => trim($this->newBoardName),
            'color'    => $this->newBoardColor,
            'position' => $position,
        ]);

        $board->columns()->createMany([
            ['name' => 'Сделать',    'position' => 0],
            ['name' => 'В процессе', 'position' => 1],
            ['name' => 'Готово',     'position' => 2],
        ]);

        $this->reset(['newBoardName', 'showCreateForm']);
        $this->newBoardColor = '#6366f1';

        $this->dispatch('toast', type: 'success', title: 'Доска создана');
    }

    public function startRename(int $boardId): void
    {
        $board = KanbanBoard::where('user_id', Auth::id())->findOrFail($boardId);
        $this->renamingBoardId   = $boardId;
        $this->renamingBoardName = $board->name;
    }

    public function saveRename(): void
    {
        if (! $this->renamingBoardId) {
            return;
        }

        $this->validate(['renamingBoardName' => 'required|string|max:100']);

        KanbanBoard::where('user_id', Auth::id())
            ->where('id', $this->renamingBoardId)
            ->update(['name' => trim($this->renamingBoardName)]);

        $this->renamingBoardId   = null;
        $this->renamingBoardName = '';
    }

    public function cancelRename(): void
    {
        $this->renamingBoardId   = null;
        $this->renamingBoardName = '';
    }

    public function setBoardColor(int $boardId, string $color): void
    {
        KanbanBoard::where('user_id', Auth::id())
            ->where('id', $boardId)
            ->update(['color' => $color === '' ? '#6366f1' : $color]);
    }

    public function deleteBoard(int $boardId): void
    {
        $board = KanbanBoard::where('user_id', Auth::id())->findOrFail($boardId);

        Task::whereIn('kanban_column_id', $board->columns()->pluck('id'))
            ->update(['kanban_column_id' => null, 'kanban_position' => 0]);

        $board->delete();

        $this->dispatch('toast', type: 'info', title: 'Доска удалена');
    }

    public function leaveBoard(int $boardId): void
    {
        $board = KanbanBoard::whereHas('members', fn ($q) => $q->where('user_id', Auth::id()))
            ->findOrFail($boardId);

        $board->members()->detach(Auth::id());

        $this->dispatch('toast', type: 'info', title: 'Вы покинули доску');
    }

    public function render()
    {
        $userId = Auth::id();

        $ownBoards = KanbanBoard::where('user_id', $userId)
            ->withCount(['columns', 'columns as tasks_count' => $this->boardCountQuery()])
            ->orderBy('position')
            ->get();

        $sharedBoards = KanbanBoard::whereHas('members', fn ($q) => $q->where('user_id', $userId))
            ->withCount(['columns', 'columns as tasks_count' => $this->boardCountQuery()])
            ->with('user:id,name')
            ->orderBy('name')
            ->get();

        return view('livewire.workspace.kanban-index', compact('ownBoards', 'sharedBoards'));
    }
}
