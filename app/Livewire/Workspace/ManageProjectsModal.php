<?php

namespace App\Livewire\Workspace;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class ManageProjectsModal extends Component
{
    public bool $open = false;

    public string $viewMode = 'list';

    public ?int $editingId = null;

    public string $name = '';

    public string $color = '#E5533A';

    public string $icon = '';

    #[On('open-manage-projects')]
    public function handleOpen(?int $projectId = null): void
    {
        $this->open = true;
        $this->resetForm();
        $this->viewMode = 'list';

        if ($projectId !== null && $projectId !== 0) {
            $project = Project::forUser(Auth::id())->find($projectId);
            if ($project) {
                $this->hydrateFromProject($project);
                $this->viewMode = 'form';
            }
        }
    }

    #[On('open-manage-projects-new')]
    public function handleOpenNew(): void
    {
        $this->open = true;
        $this->resetForm();
        $this->startCreate();
    }

    public function close(): void
    {
        $this->open = false;
        $this->resetForm();
        $this->viewMode = 'list';
    }

    public function startCreate(): void
    {
        $this->resetForm();
        $this->viewMode = 'form';
        $this->editingId = null;
        $this->name = '';
        $this->color = '#E5533A';
        $this->icon = '';
    }

    public function startEdit(int $projectId): void
    {
        $project = Project::forUser(Auth::id())->find($projectId);
        if (! $project) {
            return;
        }
        $this->resetForm();
        $this->viewMode = 'form';
        $this->hydrateFromProject($project);
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->viewMode = 'list';
    }

    protected function hydrateFromProject(?Project $project): void
    {
        if (! $project) {
            $this->startCreate();

            return;
        }

        $this->editingId = $project->id;
        $this->name = $project->name;
        $this->color = $project->color ?: '#E5533A';
        $this->icon = $project->icon ?: '';
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->color = '#E5533A';
        $this->icon = '';
    }

    public function save(): void
    {
        $this->color = $this->normalizeColor(trim($this->color));

        $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/i'],
            'icon' => ['nullable', 'string', Rule::in(array_merge([''], array_keys(Project::iconChoices())))],
        ], [], [
            'name' => 'название',
            'color' => 'цвет',
            'icon' => 'иконка',
        ]);

        $payload = [
            'name' => trim($this->name),
            'color' => $this->normalizeColor($this->color),
            'icon' => $this->icon === '' ? null : $this->icon,
        ];

        if ($this->editingId) {
            $project = Project::forUser(Auth::id())->findOrFail($this->editingId);
            $project->update($payload);
            $msg = 'Проект обновлён';
        } else {
            $max = (int) (Project::forUser(Auth::id())->max('position') ?? 0);
            Project::create(array_merge($payload, [
                'user_id' => Auth::id(),
                'position' => $max + 10,
                'is_archived' => false,
            ]));
            $msg = 'Проект создан';
        }

        $this->dispatch('toast', type: 'success', title: $msg);
        $this->dispatch('projects-updated');
        $this->cancelForm();
    }

    public function confirmDelete(int $projectId): void
    {
        $project = Project::forUser(Auth::id())->find($projectId);
        if (! $project) {
            return;
        }

        $title = $project->name;
        $project->delete();

        $this->dispatch('toast',
            type: 'info',
            title: 'Проект удалён',
            message: $title,
        );
        $this->dispatch('projects-updated');

        if ($this->editingId === $projectId) {
            $this->cancelForm();
        }
    }

    /**
     * #rgb → #rrggbb
     */
    protected function normalizeColor(string $hex): string
    {
        $hex = trim($hex);
        if (preg_match('/^#([0-9A-Fa-f]{3})$/', $hex, $m)) {
            $s = $m[1];

            return '#'.$s[0].$s[0].$s[1].$s[1].$s[2].$s[2];
        }

        return $hex;
    }

    public function render()
    {
        $iconMenuOptions = [['value' => '', 'label' => 'По умолчанию']];
        foreach (Project::iconChoices() as $key => $label) {
            $iconMenuOptions[] = [
                'value' => $key,
                'label' => $label,
                'icon' => $key,
                'iconColor' => 'var(--text-subtle)',
            ];
        }

        return view('livewire.workspace.manage-projects-modal', [
            'projects' => Project::forUser(Auth::id())
                ->active()
                ->ordered()
                ->get(),
            'iconMenuOptions' => $iconMenuOptions,
        ]);
    }
}
