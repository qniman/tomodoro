<?php

namespace App\Livewire;

use App\Models\Tag;
use App\Models\TaskCategory;
use App\Models\TaskStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PresetManager extends Component
{
    public bool $showTagModal = false;
    public bool $showCategoryModal = false;
    public bool $showStatusModal = false;
    public ?int $editingId = null;
    public ?string $editingType = null;

    public array $tagForm = ['name' => '', 'color' => '#a855f7'];
    public array $categoryForm = ['name' => '', 'color' => '#6366f1'];
    public array $statusForm = ['name' => '', 'color' => '#22c55e'];

    public function render()
    {
        return view('livewire.preset-manager', [
            'tags' => Tag::where('user_id', Auth::id())->orderBy('name')->get(),
            'categories' => TaskCategory::where('user_id', Auth::id())->orderBy('name')->get(),
            'statuses' => TaskStatus::forUserAllowed(Auth::id()),
        ]);
    }

    public function createTag(): void
    {
        $validated = $this->validate([
            'tagForm.name' => ['required', 'string', 'max:100'],
            'tagForm.color' => $this->colorRule(),
        ])['tagForm'];

        if ($this->editingType === 'tag' && $this->editingId) {
            Tag::where('user_id', Auth::id())->findOrFail($this->editingId)->update($validated);
        } else {
            Tag::create(array_merge($validated, ['user_id' => Auth::id()]));
        }

        $this->resetTagForm();
    }

    public function editTag(int $id): void
    {
        $tag = Tag::where('user_id', Auth::id())->findOrFail($id);
        $this->tagForm = $tag->only('name', 'color');
        $this->editingId = $id;
        $this->editingType = 'tag';
        $this->showTagModal = true;
    }

    public function deleteTag(int $id): void
    {
        Tag::where('user_id', Auth::id())->findOrFail($id)->delete();
    }

    public function createCategory(): void
    {
        $validated = $this->validate([
            'categoryForm.name' => ['required', 'string', 'max:100'],
            'categoryForm.color' => $this->colorRule(),
        ])['categoryForm'];

        if ($this->editingType === 'category' && $this->editingId) {
            TaskCategory::where('user_id', Auth::id())->findOrFail($this->editingId)->update($validated);
        } else {
            TaskCategory::create(array_merge($validated, ['user_id' => Auth::id()]));
        }

        $this->resetCategoryForm();
    }

    public function editCategory(int $id): void
    {
        $category = TaskCategory::where('user_id', Auth::id())->findOrFail($id);
        $this->categoryForm = $category->only('name', 'color');
        $this->editingId = $id;
        $this->editingType = 'category';
        $this->showCategoryModal = true;
    }

    public function deleteCategory(int $id): void
    {
        TaskCategory::where('user_id', Auth::id())->findOrFail($id)->delete();
    }

    public function createStatus(): void
    {
        // Status creation/editing is disabled in справочниках. Do nothing.
        $this->resetStatusForm();
    }

    // Editing statuses via UI is disabled.

    public function deleteStatus(int $id): void
    {
        // Deleting statuses is disabled. Do nothing.
        return;
    }

    public function openModal(string $type): void
    {
        $this->editingId = null;
        $this->editingType = $type;

        $handler = match ($type) {
            'tag' => function () {
                $this->tagForm = ['name' => '', 'color' => '#a855f7'];
                $this->showTagModal = true;
            },
            'category' => function () {
                $this->categoryForm = ['name' => '', 'color' => '#6366f1'];
                $this->showCategoryModal = true;
            },
            // 'status' handler removed: statuses are fixed
            default => fn () => null,
        };

        $handler();
    }

    protected function colorRule(): array
    {
        return ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'];
    }

    public function resetTagForm(bool $close = true): void
    {
        $this->tagForm = ['name' => '', 'color' => '#a855f7'];
        $this->editingId = null;
        $this->editingType = null;
        if ($close) {
            $this->showTagModal = false;
        }
    }

    public function resetCategoryForm(bool $close = true): void
    {
        $this->categoryForm = ['name' => '', 'color' => '#6366f1'];
        $this->editingId = null;
        $this->editingType = null;
        if ($close) {
            $this->showCategoryModal = false;
        }
    }

    public function resetStatusForm(bool $close = true): void
    {
        $this->statusForm = ['name' => '', 'color' => '#22c55e'];
        $this->editingId = null;
        $this->editingType = null;
        if ($close) {
            $this->showStatusModal = false;
        }
    }
}
