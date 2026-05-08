<div class="sidebar__section sidebar__tags">
    <div class="sidebar__section-title">
        <span class="sidebar__section-head">Теги</span>
        <span class="sidebar__section-actions">
            <button
                type="button"
                class="btn btn--ghost btn--icon btn--sm"
                title="Управление тегами"
                @click.prevent="Livewire.dispatch('open-manage-tags')"
                aria-label="Управление тегами"
            >
                <x-ui.icon name="list-todo" :size="14" />
            </button>
            <button
                type="button"
                class="btn btn--ghost btn--icon btn--sm"
                title="Новый тег"
                @click.prevent="Livewire.dispatch('open-manage-tags-new')"
                aria-label="Новый тег"
            >
                <x-ui.icon name="plus" :size="14" />
            </button>
        </span>
    </div>

    @forelse($tags as $tag)
        @php $iconName = $tag->displayIcon(); @endphp
        <div class="sidebar__project-row {{ $activeTagId === $tag->id ? 'is-active-project' : '' }}" wire:key="st-{{ $tag->id }}">
            <a
                href="{{ route('app.all', ['tag' => $tag->id]) }}"
                wire:navigate
                class="sidebar__link sidebar__project-link {{ $activeTagId === $tag->id ? 'is-active' : '' }}"
                title="{{ $tag->name }}"
            >
                <span class="sidebar__project-leading" style="color: {{ $tag->color }}">
                    @if($tag->icon)
                        <x-ui.icon :name="$iconName" :size="18" />
                    @else
                        <span class="sidebar__project-dot" style="background: {{ $tag->color }}"></span>
                    @endif
                </span>
                <span style="min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $tag->name }}</span>
            </a>

            <x-ui.dropdown align="right">
                <x-slot:trigger>
                    <button type="button" class="btn btn--ghost btn--icon btn--sm sidebar__project-menu" aria-label="Действия с тегом">
                        <x-ui.icon name="more-h" :size="14" />
                    </button>
                </x-slot:trigger>

                <button type="button" class="dropdown__item" @click.prevent="Livewire.dispatch('open-manage-tags', { tagId: {{ $tag->id }} })">
                    <x-ui.icon name="edit" :size="16" />
                    <span>Изменить</span>
                </button>
                <a href="{{ route('app.all', ['tag' => $tag->id]) }}" wire:navigate class="dropdown__item">
                    <x-ui.icon name="square-check" :size="16" />
                    <span>Показать задачи</span>
                </a>
            </x-ui.dropdown>
        </div>
    @empty
        <p class="sidebar__muted">Нет тегов.</p>
    @endforelse
</div>
