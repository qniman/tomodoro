<?php

namespace App\Livewire\Workspace;

use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Задачи')]
class TaskBoard extends Component
{
    /** today | inbox | upcoming | project | all */
    public string $scope = 'today';

    #[Url(as: 'project')]
    public ?int $projectId = null;

    #[Url(as: 'tag')]
    public ?int $tagId = null;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'task')]
    public ?int $selectedTaskId = null;

    public bool $showCompleted = false;

    public bool $quickAddOpen = false;
    public string $quickTitle = '';

    public function mount(string $scope = 'today'): void
    {
        $this->scope = in_array($scope, ['today', 'inbox', 'upcoming', 'all', 'project'], true) ? $scope : 'today';

        if ($this->projectId) {
            $this->scope = 'project';
        }
    }

    public function render()
    {
        return view('livewire.workspace.task-board', [
            'tasks' => $this->tasks(),
            'projects' => Project::where('user_id', Auth::id())->where('is_archived', false)->orderBy('position')->get(),
            'tags' => Tag::where('user_id', Auth::id())->orderBy('name')->get(),
            'selectedTask' => $this->selectedTask(),
            'scopeLabel' => $this->scopeLabel(),
            'scopeMeta' => $this->scopeMeta(),
        ]);
    }

    protected function tasks(): Collection
    {
        $query = Task::query()
            ->with(['tags', 'project'])
            ->forUser(Auth::id())
            ->when(! $this->showCompleted, fn (Builder $q) => $q->open());

        $this->applyScope($query);

        if ($this->search !== '') {
            $like = '%' . $this->search . '%';
            $query->where(function (Builder $q) use ($like) {
                $q->where('title', 'like', $like)
                  ->orWhere('description_text', 'like', $like);
            });
        }

        if ($this->tagId) {
            $tagId = $this->tagId;
            $query->whereHas('tags', fn (Builder $q) => $q->where('tags.id', $tagId));
        }

        $query->orderByRaw('CASE WHEN completed_at IS NULL THEN 0 ELSE 1 END')
              ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')
              ->orderBy('due_at')
              ->orderBy('position');

        return $query->get();
    }

    protected function applyScope(Builder $query): void
    {
        $today = Carbon::today();

        match ($this->scope) {
            'today' => $query->where(function (Builder $q) use ($today) {
                $q->whereDate('due_at', '<=', $today)
                  ->orWhere('is_pinned', true);
            }),
            'inbox' => $query->whereNull('project_id')->whereNull('due_at'),
            'upcoming' => $query->whereDate('due_at', '>', $today)
                                ->whereDate('due_at', '<=', $today->copy()->addDays(14)),
            'project' => $query->where('project_id', $this->projectId),
            default => null,
        };
    }

    protected function selectedTask(): ?Task
    {
        if (! $this->selectedTaskId) {
            return null;
        }

        return Task::with(['tags', 'project', 'checklist', 'attachments'])
            ->forUser(Auth::id())
            ->find($this->selectedTaskId);
    }

    public function scopeLabel(): string
    {
        return match ($this->scope) {
            'today' => 'Сегодня',
            'inbox' => 'Входящие',
            'upcoming' => 'Предстоит',
            'project' => Project::find($this->projectId)?->name ?? 'Проект',
            default => 'Все задачи',
        };
    }

    public function scopeMeta(): string
    {
        return match ($this->scope) {
            'today' => Carbon::today()->locale('ru')->isoFormat('D MMMM, dddd'),
            'upcoming' => 'на ближайшие дни',
            default => '',
        };
    }

    public function selectTask(int $taskId): void
    {
        $this->selectedTaskId = $taskId;
    }

    public function clearSelection(): void
    {
        $this->selectedTaskId = null;
    }

    public function toggleCompleted(int $taskId): void
    {
        $task = Task::forUser(Auth::id())->findOrFail($taskId);
        $wasCompleted = $task->isCompleted();
        $task->completed_at = $wasCompleted ? null : now();
        $task->save();

        $this->dispatch('toast',
            type: 'success',
            title: $wasCompleted ? 'Задача снова открыта' : 'Готово!',
            message: $task->title,
        );
    }

    #[\Livewire\Attributes\On('open-quick-add')]
    public function openQuickAdd(): void
    {
        $this->quickAddOpen = true;
        $this->quickTitle = '';
    }

    public function closeQuickAdd(): void
    {
        $this->quickAddOpen = false;
        $this->quickTitle = '';
    }

    public function createQuickTask(): void
    {
        $title = trim($this->quickTitle);
        if ($title === '') {
            $this->closeQuickAdd();
            return;
        }

        $task = new Task();
        $task->user_id = Auth::id();
        $task->title = $title;

        if ($this->scope === 'today') {
            $task->due_at = Carbon::today()->setTime(20, 0);
        }
        if ($this->scope === 'project' && $this->projectId) {
            $task->project_id = $this->projectId;
        }

        $task->position = (int) (Task::forUser(Auth::id())->max('position') ?? 0) + 10;
        $task->save();

        $this->quickTitle = '';

        $this->dispatch('toast',
            type: 'success',
            title: 'Задача добавлена',
            message: $task->title,
        );
    }

    public function deleteTask(int $taskId): void
    {
        $task = Task::forUser(Auth::id())->findOrFail($taskId);
        $title = $task->title;
        $task->delete();

        if ($this->selectedTaskId === $taskId) {
            $this->selectedTaskId = null;
        }

        $this->dispatch('toast',
            type: 'info',
            title: 'Задача удалена',
            message: $title,
            actionLabel: 'Восстановить',
            actionEvent: 'restore-task',
            actionPayload: $taskId,
            duration: 8000,
        );
    }

    #[\Livewire\Attributes\On('restore-task')]
    public function restoreTask(int $taskId): void
    {
        $task = Task::withTrashed()
            ->where('user_id', Auth::id())
            ->where('id', $taskId)
            ->first();

        if (! $task || ! $task->trashed()) return;

        $task->restore();

        $this->dispatch('toast',
            type: 'success',
            title: 'Задача восстановлена',
            message: $task->title,
        );
    }
}
