<div>
    @if($open)
        <div class="modal-backdrop" wire:click.self="close" @keydown.escape.window="$wire.close()">
            <div class="modal modal--lg" role="dialog" aria-modal="true">
                <div class="modal__header">
                    <div class="flex-1">
                        <h2 class="modal__title">
                            {{ $viewMode === 'form' ? ($editingId ? 'Проект' : 'Новый проект') : 'Проекты' }}
                        </h2>
                        @if($viewMode === 'list')
                            <p class="modal__subtitle">Создавайте, редактируйте и удаляйте списки для задач.</p>
                        @endif
                    </div>
                    <button type="button" class="btn btn--ghost btn--icon btn--sm" wire:click="close" aria-label="Закрыть">
                        <x-ui.icon name="x" :size="16" />
                    </button>
                </div>

                <div class="modal__body">
                    @if($viewMode === 'list')
                        <div class="vstack gap-3">
                            <div class="hstack gap-2" style="justify-content: flex-end;">
                                <x-ui.button variant="primary" icon="plus" wire:click="startCreate">
                                    Новый проект
                                </x-ui.button>
                            </div>

                            @forelse($projects as $p)
                                @php $ic = $p->displayIcon(); @endphp
                                <div class="project-crud-row hstack gap-2" wire:key="mp-{{ $p->id }}">
                                    <span class="sidebar__project-dot" style="background: {{ $p->color }}"></span>
                                    <span style="color: {{ $p->color }}; flex-shrink: 0;">
                                        <x-ui.icon :name="$ic" :size="18" />
                                    </span>
                                    <span class="flex-1" title="{{ $p->name }}" style="min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $p->name }}</span>
                                    <x-ui.dropdown align="right">
                                        <x-slot:trigger>
                                            <button type="button" class="btn btn--ghost btn--icon btn--sm" aria-label="Действия">
                                                <x-ui.icon name="more-h" :size="16" />
                                            </button>
                                        </x-slot:trigger>

                                        <button type="button" class="dropdown__item" wire:click="startEdit({{ $p->id }})">
                                            <x-ui.icon name="edit" :size="16" />
                                            <span>Изменить</span>
                                        </button>
                                        <div class="dropdown__separator"></div>
                                        <button
                                            type="button"
                                            class="dropdown__item dropdown__item--danger"
                                            wire:click="confirmDelete({{ $p->id }})"
                                            wire:confirm="Удалить этот проект? Задачи останутся без проекта."
                                        >
                                            <x-ui.icon name="trash" :size="16" />
                                            <span>Удалить</span>
                                        </button>
                                    </x-ui.dropdown>
                                </div>
                            @empty
                                <div class="text-muted text-center" style="padding-block: var(--s-8);">
                                    <p>Пока нет проектов.</p>
                                </div>
                            @endforelse
                        </div>
                    @else
                        <div class="vstack gap-4">
                            @if($editingId)
                                <button type="button" class="btn btn--ghost btn--sm hstack gap-1" wire:click="cancelForm" style="align-self: flex-start;">
                                    <x-ui.icon name="chevron-left" :size="16" />
                                    <span>К списку</span>
                                </button>
                            @endif

                            <div class="vstack gap-2">
                                <label class="text-sm text-subtle">Название</label>
                                <input type="text" class="input" wire:model="name" maxlength="120" placeholder="Мой проект" autocomplete="off" />
                                @error('name') <span class="text-sm" style="color: var(--danger);">{{ $message }}</span> @enderror
                            </div>

                            <div class="hstack gap-4" style="flex-wrap: wrap;">
                                <div class="vstack gap-2">
                                    <label class="text-sm text-subtle">Цвет</label>
                                    <div class="hstack gap-2" style="align-items: center;">
                                        <input type="color" wire:model.live="color" style="width: 44px; height: 36px; padding: 2px; border-radius: var(--r-2); border: 1px solid var(--border); cursor: pointer;" />
                                        <input type="text" class="input mono" wire:model.live.debounce.300ms="color" style="width: 100px;" placeholder="#RRGGBB" autocomplete="off" />
                                    </div>
                                    @error('color') <span class="text-sm" style="color: var(--danger);">{{ $message }}</span> @enderror
                                </div>

                                <div class="vstack gap-2" style="flex: 1 1 200px; min-width: 160px;">
                                    <label class="text-sm text-subtle">Иконка</label>
                                    <x-ui.menu-select property="icon" :value="$icon" :options="$iconMenuOptions" placeholder="По умолчанию" minWidth="280" />
                                    @error('icon') <span class="text-sm" style="color: var(--danger);">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="hstack gap-2" style="align-items: center; padding: var(--s-3); border-radius: var(--r-2); background: var(--surface-2);">
                                <span class="text-sm text-muted">Предпросмотр:</span>
                            @php
                                $prevIcon = ($icon !== '' && array_key_exists($icon, \App\Models\Project::iconChoices()))
                                    ? $icon
                                    : 'folder';
                            @endphp
                                <span class="sidebar__project-dot" style="background: {{ $color ?: '#E5533A' }}"></span>
                                <span style="color: {{ $color ?: '#E5533A' }}">
                                    <x-ui.icon :name="$prevIcon" :size="18" />
                                </span>
                                <span>{{ $name ?: 'Название проекта' }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                @if($viewMode === 'form')
                    <div class="modal__footer hstack gap-2" style="justify-content: flex-end;">
                        <x-ui.button variant="ghost" wire:click="cancelForm">
                            Отмена
                        </x-ui.button>
                        <x-ui.button variant="primary" icon="check" wire:click="save">
                            Сохранить
                        </x-ui.button>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
