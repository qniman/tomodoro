<?php

namespace App\Livewire;

use App\Models\Tag;
use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\TaskStatus;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Livewire\Component;

class TodoManager extends Component
{
    public array $filters = [];
    public array $filterDraft = [];
    public array $taskForm = [];
    public array $selectedTagIds = [];
    public ?int $editingTaskId = null;
    public ?string $selectedStatusFilter = null;

    public bool $showTaskModal = false;
    public bool $showDeleteModal = false;
    public bool $showTagModal = false;
    public bool $showCategoryModal = false;
    public bool $showStatusModal = false;
    public bool $isEditing = false;
    public ?int $deletingTaskId = null;

    public array $tagForm = ['name' => '', 'color' => '#a855f7'];
    public array $categoryForm = ['name' => '', 'color' => '#6366f1'];
    public array $statusForm = ['name' => '', 'color' => '#22c55e'];

    public array $priorityOptions = [
        'low' => 'Низкий',
        'medium' => 'Средний',
        'high' => 'Высокий',
    ];

    public function mount(): void
    {
        $defaults = $this->defaultFilters();
        $this->filters = $defaults;
        $this->filterDraft = $defaults;
        $this->resetForm();

        if ($taskId = request()->integer('task')) {
            $this->editTask($taskId);
        }
    }

    public function render()
    {
        $categoryOptions = $this->categoryOptions();
        $statusOptions = $this->statusOptions();

        return view('livewire.todo-manager', [
            'tasks' => $this->tasks(),
            'tags' => $this->tags(),
            'categoryOptions' => $categoryOptions,
            'statusOptions' => $statusOptions,
            'selectedStatusFilter' => $this->selectedStatusFilter,
        ]);
    }

    public function tasks()
    {
        return Task::with('tags')
            ->where('user_id', Auth::id())
            ->when($this->filters['search'], fn ($query) => $query->where('title', 'like', "%{$this->filters['search']}%"))
            ->when($this->filters['category'], fn ($query) => $query->where('category', $this->filters['category']))
            ->when($this->filters['priority'], fn ($query) => $query->where('priority', $this->filters['priority']))
            ->when($this->selectedStatusFilter, fn ($query) => $query->where('status', $this->selectedStatusFilter))
            ->orderByRaw('due_at is null')
            ->orderBy('due_at')
            ->get();
    }

    public function tags()
    {
        return Tag::where('user_id', Auth::id())->orderBy('name')->get();
    }

    public function openCreateTask(): void
    {
        $this->resetForm();
        $this->showTaskModal = true;
        $this->isEditing = false;
    }

    public function saveTask(): void
    {
        $this->validate($this->rules());

        $payload = array_merge($this->taskForm, ['user_id' => Auth::id()]);
        // Ensure DB-required numeric fields exist
        if (! array_key_exists('est_minutes', $payload) || $payload['est_minutes'] === null) {
            $payload['est_minutes'] = 0;
        }
        if (! array_key_exists('actual_minutes', $payload) || $payload['actual_minutes'] === null) {
            $payload['actual_minutes'] = 0;
        }
        if (! empty($payload['created_at'])) {
            // Normalize datetime-local value to proper datetime string
            $payload['created_at'] = Carbon::parse($payload['created_at'])->toDateTimeString();
        }
        $task = $this->editingTaskId
            ? Task::where('user_id', Auth::id())->findOrFail($this->editingTaskId)
            : new Task();

        $task->fill($payload);
        $task->save();

        $tagIds = collect($this->selectedTagIds)->filter()->map(fn ($id) => (int) $id)->all();
        $task->tags()->sync($tagIds);

        $this->resetForm();
        $this->showTaskModal = false;
    }

    public function editTask($taskId): void
    {
        $task = Task::where('user_id', Auth::id())->findOrFail($taskId);

        $this->editingTaskId = $task->id;
        $this->taskForm = $task->only([
            'title', 'description', 'category', 'priority', 'status', 'due_at', 'created_at',
        ]);
        $this->selectedTagIds = $task->tags()->pluck('tags.id')->map(fn ($id) => (string) $id)->toArray();
        $this->showTaskModal = true;
        $this->isEditing = true;
    }

    public function requestDeleteTask($taskId): void
    {
        $this->deletingTaskId = $taskId;
        $this->showDeleteModal = true;
    }

    public function deleteTaskConfirmed(): void
    {
        if ($this->deletingTaskId) {
            Task::where('user_id', Auth::id())->findOrFail($this->deletingTaskId)->delete();
        }

        $this->showDeleteModal = false;
        $this->deletingTaskId = null;
    }

    public function createTag(): void
    {
        $validated = $this->validate([
            'tagForm.name' => ['required', 'string', 'max:100'],
            'tagForm.color' => $this->colorRule(),
        ]);

        Tag::updateOrCreate(
            ['user_id' => Auth::id(), 'name' => $validated['tagForm']['name']],
            ['color' => $validated['tagForm']['color']]
        );

        $this->resetTagForm();
        $this->showTagModal = false;
    }

    public function createCategory(): void
    {
        $validated = $this->validate([
            'categoryForm.name' => ['required', 'string', 'max:100'],
            'categoryForm.color' => $this->colorRule(),
        ]);

        TaskCategory::updateOrCreate(
            ['user_id' => Auth::id(), 'name' => $validated['categoryForm']['name']],
            ['color' => $validated['categoryForm']['color']]
        );

        $this->resetCategoryForm();
        $this->showCategoryModal = false;
    }

    public function createStatus(): void
    {
        // Creating custom statuses via UI is disabled. Only predefined statuses are allowed.
        $this->resetStatusForm();
        $this->showStatusModal = false;
    }

    protected function rules(): array
    {
        return [
            'taskForm.title' => ['required', 'string', 'max:255'],
            'taskForm.description' => ['nullable', 'string'],
            'taskForm.category' => ['nullable', 'string', 'max:100'],
            'taskForm.priority' => ['required', 'in:low,medium,high'],
            'taskForm.due_at' => ['nullable', 'date'],
            'taskForm.created_at' => ['required', 'date'],
        ];
    }

    protected function colorRule(): array
    {
        return ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'];
    }

    protected function resetForm(): void
    {
        $this->editingTaskId = null;
        $this->taskForm = [
            'title' => '',
            'description' => '',
            'category' => $this->defaultCategoryName(),
            'priority' => 'medium',
            'status' => $this->defaultStatusName(),
            'due_at' => null,
            'created_at' => now()->format('Y-m-d\\TH:i'),
        ];
        $this->selectedTagIds = [];
        $this->isEditing = false;
    }

    protected function resetTagForm(): void
    {
        $this->tagForm = ['name' => '', 'color' => '#a855f7'];
    }

    protected function resetCategoryForm(): void
    {
        $this->categoryForm = ['name' => '', 'color' => '#6366f1'];
    }

    protected function resetStatusForm(): void
    {
        $this->statusForm = ['name' => '', 'color' => '#22c55e'];
    }

    public function applyFilters(): void
    {
        $this->filters = $this->filterDraft;
    }

    public function resetFilters(): void
    {
        $defaults = $this->defaultFilters();
        $this->filters = $defaults;
        $this->filterDraft = $defaults;
    }

    protected function categoryOptions()
    {
        return TaskCategory::where('user_id', Auth::id())->orderBy('name')->get();
    }

    protected function statusOptions()
    {
        return TaskStatus::forUserAllowed(Auth::id());
    }

    protected function defaultCategoryName(): ?string
    {
        return TaskCategory::where('user_id', Auth::id())->orderBy('name')->value('name');
    }

    protected function defaultStatusName(): string
    {
        return TaskStatus::where('user_id', Auth::id())->whereIn('name', TaskStatus::allowedNames())->orderBy('name')->value('name') ?? 'Новое';
    }

    protected function defaultFilters(): array
    {
        return [
            'search' => '',
            'status' => '',
            'priority' => '',
            'category' => '',
        ];
    }

    public function setStatusFilter(?string $status): void
    {
        $this->selectedStatusFilter = $status;
    }
}
