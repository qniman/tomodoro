<?php

namespace App\Http\Controllers\Todo;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdateRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\Todo\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(protected TaskService $tasks) {}

    public function index(Request $request)
    {
        $tasks = $this->tasks->listFor($request->user(), $request->only(['search', 'category', 'status', 'priority', 'tag']));

        return TaskResource::collection($tasks);
    }

    public function store(TaskStoreRequest $request)
    {
        $task = $this->tasks->create($request->user(), $request->validated());

        return new TaskResource($task);
    }

    public function show(Task $task)
    {
        $this->ensureOwnership($task);

        return new TaskResource($task->load('tags'));
    }

    public function update(TaskUpdateRequest $request, Task $task)
    {
        $this->ensureOwnership($task);

        $updated = $this->tasks->update($task, $request->validated());

        return new TaskResource($updated);
    }

    public function destroy(Task $task)
    {
        $this->ensureOwnership($task);

        $this->tasks->delete($task);

        return response()->noContent();
    }

    protected function ensureOwnership(Task $task): void
    {
        abort_unless(auth()->id() === $task->user_id, 403);
    }
}
