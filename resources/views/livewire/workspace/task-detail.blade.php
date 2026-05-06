<div class="task-detail">
    @if($task)
        <div class="task-detail__header">
            <span style="margin-top: 6px;" wire:click="toggleCompleted">
                <x-ui.checkbox round :checked="$task->isCompleted()" />
            </span>

            <input
                type="text"
                class="task-detail__title-input {{ $task->isCompleted() ? 'is-completed' : '' }}"
                wire:model.blur="title"
                value="{{ $title }}"
                maxlength="255"
                placeholder="Название задачи"
            />

            <div class="hstack gap-1">
                <button
                    type="button"
                    class="btn btn--ghost btn--icon btn--sm"
                    wire:click="togglePinned"
                    aria-label="Закрепить"
                    style="{{ $isPinned ? 'color: var(--accent);' : '' }}"
                >
                    <x-ui.icon name="pin" :size="16" />
                </button>
                <button
                    type="button"
                    class="btn btn--ghost btn--icon btn--sm"
                    wire:click="$parent.clearSelection"
                    aria-label="Закрыть"
                >
                    <x-ui.icon name="x" :size="16" />
                </button>
            </div>
        </div>

        <div class="task-detail__body">
            {{-- Метаданные --}}
            <div class="task-detail__meta">
                <label class="meta-row" style="cursor: pointer;">
                    <x-ui.icon name="calendar" :size="16" />
                    <input
                        type="datetime-local"
                        class="input"
                        style="border: 0; padding: 0; background: transparent; box-shadow: none;"
                        wire:model.blur="dueAt"
                    />
                </label>

                <label class="meta-row" style="cursor: pointer;">
                    <x-ui.icon name="flag" :size="16" />
                    <select wire:model.live="priority" class="select" style="border: 0; padding: 0 var(--s-7) 0 0; background-color: transparent; box-shadow: none;">
                        <option value="low">Низкий</option>
                        <option value="normal">Обычный</option>
                        <option value="high">Высокий</option>
                        <option value="urgent">Срочный</option>
                    </select>
                </label>

                <label class="meta-row" style="cursor: pointer;">
                    <x-ui.icon name="clock" :size="16" />
                    <input
                        type="number"
                        min="0"
                        step="5"
                        class="input"
                        style="border: 0; padding: 0; background: transparent; box-shadow: none; max-width: 80px;"
                        placeholder="Оценка"
                        wire:model.blur="estimatedMinutes"
                    />
                    <span class="text-subtle">мин</span>
                </label>

                <button
                    type="button"
                    class="meta-row"
                    style="cursor: pointer; color: var(--accent);"
                    wire:click="startPomodoro"
                >
                    <x-ui.icon name="timer" :size="16" />
                    <span>Запустить помодоро</span>
                </button>
            </div>

            {{-- Описание (rich) --}}
            <div>
                <div class="task-detail__section-title">
                    <x-ui.icon name="file-text" :size="14" />
                    <span>Описание</span>
                </div>

                {{--
                    Обёртка с wire:key вне ignore: при смене задачи блок целиком пересоздаётся → новый Alpine/Tiptap.
                    wire:ignore на самом .editor: при обновлениях того же задачи Livewire не трогает DOM ProseMirror.
                --}}
                <div wire:key="editor-shell-{{ $task->id }}"
                     class="editor-host"
                     x-data="richEditor($wire, 'descriptionHtml')"
                     x-init="init()"
                >
                    <div class="editor" wire:ignore>
                        <div class="editor__toolbar" data-toolbar></div>
                        <div class="editor__content" data-content></div>
                    </div>
                </div>
            </div>

            {{-- Чеклист --}}
            <div>
                <div class="task-detail__section-title">
                    <x-ui.icon name="list-todo" :size="14" />
                    <span>Чек-лист</span>
                </div>

                <div class="checklist">
                    @foreach($task->checklist as $item)
                        <div class="checklist__item {{ $item->is_done ? 'is-done' : '' }}" wire:key="cl-{{ $item->id }}">
                            <x-ui.checkbox :checked="$item->is_done" wire:click="toggleChecklistItem({{ $item->id }})" />
                            <span class="checklist__label">{{ $item->label }}</span>
                            <button type="button"
                                    class="btn btn--ghost btn--icon btn--sm checklist__delete"
                                    wire:click="deleteChecklistItem({{ $item->id }})"
                                    aria-label="Удалить">
                                <x-ui.icon name="x" :size="14" />
                            </button>
                        </div>
                    @endforeach

                    <form class="checklist__add" wire:submit.prevent="addChecklistItem">
                        <x-ui.icon name="plus" :size="16" />
                        <input
                            type="text"
                            class="checklist__label"
                            placeholder="Добавить пункт…"
                            wire:model="newChecklistLabel"
                            maxlength="200"
                        />
                    </form>
                </div>
            </div>

            {{-- Аттачи --}}
            <div>
                <div class="task-detail__section-title">
                    <x-ui.icon name="paperclip" :size="14" />
                    <span>Файлы</span>
                </div>

                <div class="attachments">
                    @forelse($task->attachments as $att)
                        <div class="attachment" wire:key="att-{{ $att->id }}">
                            <div class="attachment__icon">
                                @if($att->isImage())
                                    <img src="{{ $att->url }}" alt="" />
                                @else
                                    <x-ui.icon name="file" :size="18" />
                                @endif
                            </div>
                            <div class="attachment__body">
                                <a href="{{ $att->url }}" target="_blank" class="attachment__name">{{ $att->original_name }}</a>
                                <div class="attachment__meta">{{ $att->formatted_size }}</div>
                            </div>
                            <button class="btn btn--ghost btn--icon btn--sm attachment__delete"
                                    type="button"
                                    wire:click="deleteAttachment({{ $att->id }})"
                                    aria-label="Удалить">
                                <x-ui.icon name="trash" :size="14" />
                            </button>
                        </div>
                    @empty
                        {{-- ничего --}}
                    @endforelse

                    <label class="dropzone"
                           x-data="{ over: false }"
                           :class="{ 'is-active': over }"
                           @dragover.prevent="over = true"
                           @dragleave.prevent="over = false"
                           @drop.prevent="
                                over = false;
                                if ($event.dataTransfer?.files?.length) {
                                    const input = $el.querySelector('input[type=file]');
                                    input.files = $event.dataTransfer.files;
                                    input.dispatchEvent(new Event('change', { bubbles: true }));
                                }
                           ">
                        <input
                            type="file"
                            multiple
                            wire:model="uploadedFiles"
                            class="dropzone__input"
                        />
                        <div class="hstack" style="justify-content: center;" wire:loading.remove wire:target="uploadedFiles">
                            <x-ui.icon name="upload" :size="18" />
                            <span>Перетащите файл или нажмите, чтобы загрузить</span>
                        </div>
                        <div wire:loading wire:target="uploadedFiles" class="hstack" style="justify-content: center; color: var(--accent);">
                            <x-ui.icon name="upload" :size="16" />
                            <span>Загрузка…</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    @else
        <div class="task-detail__body" style="text-align: center; padding-top: var(--s-8);">
            <p class="text-muted">Выберите задачу, чтобы посмотреть детали.</p>
        </div>
    @endif
</div>
