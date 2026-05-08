@if($viewMode === 'list')
    <div class="vstack gap-3">
        <div class="hstack gap-2" style="justify-content: flex-end;">
            <x-ui.button variant="primary" icon="plus" wire:click="startCreate">
                Новый тег
            </x-ui.button>
        </div>

        @forelse($tags as $t)
            @php $ic = $t->displayIcon(); @endphp
            <div class="project-crud-row hstack gap-2" wire:key="mt-{{ $t->id }}">
                <span class="sidebar__project-dot" style="background: {{ $t->color }}"></span>
                <span style="color: {{ $t->color }}; flex-shrink: 0;">
                    <x-ui.icon :name="$ic" :size="18" />
                </span>
                <span class="flex-1" title="{{ $t->name }}" style="min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $t->name }}</span>
                <x-ui.dropdown align="right">
                    <x-slot:trigger>
                        <button type="button" class="btn btn--ghost btn--icon btn--sm" aria-label="Действия">
                            <x-ui.icon name="more-h" :size="16" />
                        </button>
                    </x-slot:trigger>

                    <button type="button" class="dropdown__item" wire:click="startEdit({{ $t->id }})">
                        <x-ui.icon name="edit" :size="16" />
                        <span>Изменить</span>
                    </button>
                    <div class="dropdown__separator"></div>
                    <button
                        type="button"
                        class="dropdown__item dropdown__item--danger"
                        wire:click="confirmDelete({{ $t->id }})"
                        wire:confirm="Удалить этот тег? Он будет снят с задач."
                    >
                        <x-ui.icon name="trash" :size="16" />
                        <span>Удалить</span>
                    </button>
                </x-ui.dropdown>
            </div>
        @empty
            <div class="text-muted text-center" style="padding-block: var(--s-8);">
                <p>Тегов пока нет.</p>
                <p class="mt-2">
                    <x-ui.button variant="primary" icon="plus" wire:click="startCreate">
                        Создать первый
                    </x-ui.button>
                </p>
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
            <label class="text-sm text-subtle">Название (до 20 символов)</label>
            <input type="text" class="input" wire:model="name" maxlength="20" placeholder="работа" autocomplete="off" />
            @error('name') <span class="text-sm" style="color: var(--danger);">{{ $message }}</span> @enderror
        </div>

        <div class="hstack gap-4" style="flex-wrap: wrap;">
            <div class="vstack gap-2">
                <label class="text-sm text-subtle">Цвет</label>
                <div class="hstack gap-2" style="align-items: center;">
                    <input type="color" wire:model="color" style="width: 44px; height: 36px; padding: 2px; border-radius: var(--r-2); border: 1px solid var(--border); cursor: pointer;" />
                    <input type="text" class="input mono" wire:model.blur="color" style="width: 100px;" placeholder="#RRGGBB" autocomplete="off" />
                </div>
                @error('color') <span class="text-sm" style="color: var(--danger);">{{ $message }}</span> @enderror
            </div>

            <div class="vstack gap-2" style="flex: 1 1 200px; min-width: 160px;">
                <label class="text-sm text-subtle">Иконка</label>
                <x-ui.menu-select property="icon" :value="$icon" :options="$iconMenuOptions" placeholder="По умолчанию" minWidth="280" />
                @error('icon') <span class="text-sm" style="color: var(--danger);">{{ $message }}</span> @enderror
            </div>
        </div>

        @php
            $prevIcon = ($icon !== '' && array_key_exists($icon, \App\Models\Tag::iconChoices()))
                ? $icon
                : 'tag';
        @endphp
        <div class="hstack gap-2" style="align-items: center; padding: var(--s-3); border-radius: var(--r-2); background: var(--surface-2);">
            <span class="text-sm text-muted">Предпросмотр:</span>
            <span class="sidebar__project-dot" style="background: {{ $color ?: '#94A3B8' }}"></span>
            <span style="color: {{ $color ?: '#94A3B8' }}">
                <x-ui.icon :name="$prevIcon" :size="18" />
            </span>
            <span>{{ $name ?: 'название' }}</span>
        </div>
    </div>
@endif
