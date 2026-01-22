<?php

namespace App\Services\Todo;

use App\Models\Task;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class TaskService
{
    public function listFor(User $user, array $filters = []): Collection
    {
        $query = $user->tasks()->with('tags');

        if ($search = Arr::get($filters, 'search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($category = Arr::get($filters, 'category')) {
            $query->where('category', $category);
        }

        if ($status = Arr::get($filters, 'status')) {
            $query->where('status', $status);
        }

        if ($priority = Arr::get($filters, 'priority')) {
            $query->where('priority', $priority);
        }

        if ($tag = Arr::get($filters, 'tag')) {
            $query->whereHas('tags', fn ($q) => $q->where('name', $tag));
        }

        return $query->latest('due_at')->get();
    }

    public function create(User $user, array $data): Task
    {
        $tags = Arr::pull($data, 'tag_ids', []);
        $task = $user->tasks()->create($data);
        $this->syncTags($task, $tags);

        return $task->load('tags');
    }

    public function update(Task $task, array $data): Task
    {
        $tags = Arr::pull($data, 'tag_ids', []);
        $task->fill($data);
        $task->save();
        $this->syncTags($task, $tags);

        return $task->load('tags');
    }

    public function delete(Task $task): void
    {
        $task->tags()->detach();
        $task->delete();
    }

    protected function syncTags(Task $task, array $tags): void
    {
        $tagIds = Tag::query()
            ->whereIn('id', $tags)
            ->get()
            ->pluck('id')
            ->toArray();

        $task->tags()->sync($tagIds);
    }
}
