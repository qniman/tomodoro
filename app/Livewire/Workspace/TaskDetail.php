<?php

namespace App\Livewire\Workspace;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskChecklistItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class TaskDetail extends Component
{
    use WithFileUploads;

    public ?int $taskId = null;

    public string $title = '';
    public ?string $descriptionHtml = '';
    public ?string $dueAt = null;
    public ?int $projectId = null;
    public string $priority = 'normal';
    public ?int $estimatedMinutes = null;
    public bool $isPinned = false;

    public string $newChecklistLabel = '';
    public $uploadedFiles = [];

    public function mount(?int $taskId = null): void
    {
        $this->taskId = $taskId;
        $this->loadTask();
    }

    #[On('task-selected')]
    public function onTaskSelected(int $taskId): void
    {
        $this->taskId = $taskId;
        $this->loadTask();
    }

    protected function task(): ?Task
    {
        if (! $this->taskId) {
            return null;
        }

        return Task::with(['checklist', 'attachments', 'tags', 'project'])
            ->forUser(Auth::id())
            ->find($this->taskId);
    }

    protected function loadTask(): void
    {
        $task = $this->task();
        if (! $task) {
            $this->title = '';
            $this->descriptionHtml = '';
            $this->dueAt = null;
            $this->projectId = null;
            $this->priority = 'normal';
            $this->estimatedMinutes = null;
            $this->isPinned = false;
            return;
        }

        $this->title = (string) $task->title;
        $this->descriptionHtml = (string) ($task->description_html ?? '');
        $this->dueAt = $task->due_at?->format('Y-m-d\\TH:i');
        $this->projectId = $task->project_id;
        $this->priority = $task->priority ?? 'normal';
        $this->estimatedMinutes = $task->estimated_minutes;
        $this->isPinned = (bool) $task->is_pinned;
    }

    public function updatedTitle($value): void
    {
        $task = $this->task();
        if ($task) {
            $task->title = trim((string) $value) ?: $task->title;
            $task->save();
        }
    }

    public function updatedDescriptionHtml($value): void
    {
        $task = $this->task();
        if ($task) {
            $task->description_html = $value;
            $task->save();
        }
    }

    public function updatedDueAt($value): void
    {
        $task = $this->task();
        if ($task) {
            $task->due_at = $value ? Carbon::parse($value) : null;
            $task->save();
        }
    }

    public function updatedProjectId($value): void
    {
        $task = $this->task();
        if ($task) {
            $task->project_id = $value ?: null;
            $task->save();
        }
    }

    public function updatedPriority($value): void
    {
        if (! in_array($value, Task::PRIORITIES, true)) {
            return;
        }
        $task = $this->task();
        if ($task) {
            $task->priority = $value;
            $task->save();
        }
    }

    public function updatedEstimatedMinutes($value): void
    {
        $task = $this->task();
        if ($task) {
            $task->estimated_minutes = $value ? (int) $value : null;
            $task->save();
        }
    }

    public function togglePinned(): void
    {
        $task = $this->task();
        if (! $task) return;
        $task->is_pinned = ! $task->is_pinned;
        $task->save();
        $this->isPinned = $task->is_pinned;
    }

    public function toggleCompleted(): void
    {
        $task = $this->task();
        if (! $task) return;
        $task->completed_at = $task->isCompleted() ? null : now();
        $task->save();

        $this->dispatch('toast',
            type: 'success',
            title: $task->isCompleted() ? 'Задача завершена' : 'Задача снова открыта',
            message: $task->title,
        );
    }

    public function addChecklistItem(): void
    {
        $task = $this->task();
        if (! $task) return;
        $label = trim($this->newChecklistLabel);
        if ($label === '') return;

        $position = ((int) $task->checklist()->max('position')) + 10;
        TaskChecklistItem::create([
            'task_id' => $task->id,
            'label' => $label,
            'position' => $position,
        ]);

        $this->newChecklistLabel = '';
    }

    public function toggleChecklistItem(int $itemId): void
    {
        $task = $this->task();
        if (! $task) return;
        $item = $task->checklist()->find($itemId);
        if (! $item) return;
        $item->is_done = ! $item->is_done;
        $item->save();
    }

    public function deleteChecklistItem(int $itemId): void
    {
        $task = $this->task();
        if (! $task) return;
        $task->checklist()->where('id', $itemId)->delete();
    }

    public function updatedUploadedFiles(): void
    {
        $task = $this->task();
        if (! $task || ! is_array($this->uploadedFiles)) return;

        foreach ($this->uploadedFiles as $file) {
            if (! $file) continue;
            $stored = $file->store('attachments/'.Auth::id(), 'public');
            TaskAttachment::create([
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'disk' => 'public',
                'path' => $stored,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        $this->uploadedFiles = [];

        $this->dispatch('toast',
            type: 'success',
            title: 'Файл загружен',
        );
    }

    public function deleteAttachment(int $attachmentId): void
    {
        $task = $this->task();
        if (! $task) return;
        $att = $task->attachments()->find($attachmentId);
        if (! $att) return;

        try { Storage::disk($att->disk)->delete($att->path); } catch (\Throwable $e) { /* ignore */ }
        $att->delete();
    }

    public function startPomodoro(): void
    {
        if (! $this->taskId) return;
        $this->dispatch('pomodoro:start', taskId: $this->taskId);
        $this->dispatch('toast',
            type: 'success',
            title: 'Помодоро запущен',
        );
    }

    public function close(): void
    {
        $this->dispatch('task-detail-closed');
    }

    public function render()
    {
        return view('livewire.workspace.task-detail', [
            'task' => $this->task(),
        ]);
    }
}
