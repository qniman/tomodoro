<div class="sidebar__section">
    <div class="sidebar__section-title">
        <span class="sidebar__section-head">Проекты</span>
        <span class="sidebar__section-actions">
            <button
                type="button"
                class="btn btn--ghost btn--icon btn--sm"
                title="Управление проектами"
                @click.prevent="Livewire.dispatch('open-manage-projects')"
                aria-label="Управление проектами"
            >
                <x-ui.icon name="list-todo" :size="14" />
            </button>
            <button
                type="button"
                class="btn btn--ghost btn--icon btn--sm"
                title="Новый проект"
                @click.prevent="Livewire.dispatch('open-manage-projects-new')"
                aria-label="Новый проект"
            >
                <x-ui.icon name="plus" :size="14" />
            </button>
        </span>
    </div>

    @forelse($projects as $project)
        @php($iconName = $project->displayIcon())
        <div class="sidebar__project-row {{ $activeProjectId === $project->id ? 'is-active-project' : '' }}" wire:key="sp-{{ $project->id }}">
            <a
                href="{{ route('app.all', ['project' => $project->id]) }}"
                wire:navigate
                class="sidebar__link sidebar__project-link {{ $activeProjectId === $project->id ? 'is-active' : '' }}"
                title="{{ $project->name }}"
            >
                <span class="sidebar__project-leading" style="color: {{ $project->color }}">
                    @if($project->icon)
                        <x-ui.icon :name="$iconName" :size="18" />
                    @else
                        <span class="sidebar__project-dot" style="background: {{ $project->color }}"></span>
                    @endif
                </span>
                <span class="sidebar__link-text" style="min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $project->name }}</span>
            </a>

            <x-ui.dropdown align="right">
                <x-slot:trigger>
                    <button type="button" class="btn btn--ghost btn--icon btn--sm sidebar__project-menu" aria-label="Действия с проектом">
                        <x-ui.icon name="more-h" :size="14" />
                    </button>
                </x-slot:trigger>

                <button type="button" class="dropdown__item" @click.prevent="Livewire.dispatch('open-manage-projects', { projectId: {{ $project->id }} })">
                    <x-ui.icon name="edit" :size="16" />
                    <span>Изменить</span>
                </button>
                <a href="{{ route('app.all', ['project' => $project->id]) }}" wire:navigate class="dropdown__item">
                    <x-ui.icon name="square-check" :size="16" />
                    <span>Открыть задачи</span>
                </a>
            </x-ui.dropdown>
        </div>
    @empty
        <p class="sidebar__muted">Нет проектов.</p>
    @endforelse
</div>
