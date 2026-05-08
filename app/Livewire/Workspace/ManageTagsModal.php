<?php

namespace App\Livewire\Workspace;

use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class ManageTagsModal extends Component
{
    /** Встройка во вкладку «Настройки → Теги» без модальной оболочки. */
    public bool $embedded = false;

    public bool $open = false;

    public string $viewMode = 'list';

    public ?int $editingId = null;

    public string $name = '';

    public string $color = '#94A3B8';

    public string $icon = '';

    #[On('open-manage-tags')]
    public function handleOpen(?int $tagId = null): void
    {
        $this->open = true;
        $this->resetForm();
        $this->viewMode = 'list';

        if ($tagId !== null && $tagId !== 0) {
            $tag = Tag::forUser(Auth::id())->find($tagId);
            if ($tag) {
                $this->hydrateFromTag($tag);
                $this->viewMode = 'form';
            }
        }
    }

    #[On('open-manage-tags-new')]
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
        $this->color = '#94A3B8';
        $this->icon = '';
    }

    public function startEdit(int $tagId): void
    {
        $tag = Tag::forUser(Auth::id())->find($tagId);
        if (! $tag) {
            return;
        }
        $this->resetForm();
        $this->viewMode = 'form';
        $this->hydrateFromTag($tag);
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->viewMode = 'list';
    }

    protected function hydrateFromTag(?Tag $tag): void
    {
        if (! $tag) {
            $this->startCreate();

            return;
        }

        $this->editingId = $tag->id;
        $this->name = $tag->name;
        $this->color = $tag->color ?: '#94A3B8';
        $this->icon = $tag->icon ?: '';
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->color = '#94A3B8';
        $this->icon = '';
    }

    public function save(): void
    {
        $this->color = $this->normalizeColor(trim($this->color));

        $this->validate([
            'name' => [
                'required',
                'string',
                'max:20',
                Rule::unique('tags', 'name')
                    ->where(fn ($q) => $q->where('user_id', Auth::id()))
                    ->ignore($this->editingId),
            ],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/i'],
            'icon' => ['nullable', 'string', Rule::in(array_merge([''], array_keys(Tag::iconChoices())))],
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
            $tag = Tag::forUser(Auth::id())->findOrFail($this->editingId);
            $tag->update($payload);
            $msg = 'Тег обновлён';
        } else {
            Tag::create(array_merge($payload, ['user_id' => Auth::id()]));
            $msg = 'Тег создан';
        }

        $this->dispatch('toast', type: 'success', title: $msg);
        $this->dispatch('tags-updated');
        $this->cancelForm();
    }

    public function confirmDelete(int $tagId): void
    {
        $tag = Tag::forUser(Auth::id())->find($tagId);
        if (! $tag) {
            return;
        }

        $title = $tag->name;
        $tag->delete();

        $this->dispatch('toast',
            type: 'info',
            title: 'Тег удалён',
            message: $title,
        );
        $this->dispatch('tags-updated');

        if ($this->editingId === $tagId) {
            $this->cancelForm();
        }
    }

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
        foreach (Tag::iconChoices() as $key => $label) {
            $iconMenuOptions[] = [
                'value' => $key,
                'label' => $label,
                'icon' => $key,
                'iconColor' => 'var(--text-subtle)',
            ];
        }

        return view('livewire.workspace.manage-tags-modal', [
            'tags' => Tag::forUser(Auth::id())->ordered()->get(),
            'iconMenuOptions' => $iconMenuOptions,
        ]);
    }
}
