<div
    class="task-board-page"
    x-data="taskBoardSplit({ minWidth: 280, maxWidth: 640 })"
    @mousemove.window="onMove($event)"
    @mouseup.window="endResize()"
    @keydown.escape.window="if (!$wire.quickAddOpen && $wire.selectedTaskId) $wire.clearSelection()"
>
    <header class="workspace__header workspace__header--task-board">
        <div class="workspace__title">
            <span>{{ $scopeLabel }}</span>
            @if($scopeMeta)
                <span class="workspace__title-meta task-board-page__scope-meta">· {{ $scopeMeta }}</span>
            @endif
            <span class="badge task-board-page__badge-count" style="margin-left: var(--s-2)">
                {{ $tasks->count() }} {{ trans_choice('задача|задачи|задач', $tasks->count()) }}
            </span>
        </div>

        <div class="hstack gap-2 task-board-page__toolbar">
            <div class="input-group task-board-page__search" style="height: 36px; min-width: 240px;">
                <span class="input-group__addon"><x-ui.icon name="search" :size="16" /></span>
                    <input
                        type="search"
                        class="input"
                        placeholder="Поиск задачи…"
                        wire:model.live.debounce.300ms="search"
                        autocomplete="off"
                    />
            </div>

            <x-ui.button
                variant="ghost"
                icon="{{ $showCompleted ? 'eye' : 'eye-off' }}"
                size="sm"
                wire:click="$toggle('showCompleted')"
                class="task-board-page__btn-completed-wide"
                title="{{ $showCompleted ? 'Скрыть завершённые' : 'Показать завершённые' }}"
            >
                {{ $showCompleted ? 'Скрыть завершённые' : 'Показать завершённые' }}
            </x-ui.button>

            <x-ui.button
                variant="ghost"
                icon="{{ $showCompleted ? 'eye' : 'eye-off' }}"
                size="sm"
                wire:click="$toggle('showCompleted')"
                iconOnly
                class="task-board-page__btn-completed-narrow"
                title="{{ $showCompleted ? 'Скрыть завершённые' : 'Показать завершённые' }}"
                aria-label="{{ $showCompleted ? 'Скрыть завершённые' : 'Показать завершённые' }}"
            ></x-ui.button>

            <x-ui.button variant="primary" icon="plus" size="sm" wire:click="openQuickAdd">
                <span class="task-board-page__label-wide">Новая задача</span>
                <span class="task-board-page__label-narrow">Добавить</span>
            </x-ui.button>
        </div>
    </header>

    <div
        class="workspace__main workspace__main--task-board"
        x-bind:style="taskBoardRailStyle()"
    >
        <section class="task-board__pane">
            {{-- Quick add panel --}}
            @if($quickAddOpen)
                <form
                    class="task-quick task-quick--editing"
                    wire:submit.prevent="createQuickTask"
                    x-data
                    x-init="$nextTick(() => $refs.input?.focus())"
                    @keydown.escape.window="$wire.closeQuickAdd()"
                >
                    <input
                        x-ref="input"
                        wire:model="quickTitle"
                        type="text"
                        class="task-quick__title-input"
                        placeholder="Название задачи… (Enter — сохранить, Esc — отмена)"
                        maxlength="255"
                        autocomplete="off"
                    />
                    <div class="task-quick__row">
                        <x-ui.button variant="primary" type="submit" size="sm" icon="plus">Добавить</x-ui.button>
                        <x-ui.button variant="ghost" size="sm" type="button" wire:click="closeQuickAdd">Отмена</x-ui.button>
                    </div>
                </form>
            @else
                <button class="task-quick" type="button" wire:click="openQuickAdd">
                    <x-ui.icon name="plus" :size="16" />
                    <span>Добавить задачу</span>
                </button>
            @endif

            <div class="task-board__list-scroll">
                <div class="task-list" wire:key="task-list">
                    @forelse($tasks as $task)
                        @php
                            $isCompleted = $task->isCompleted();
                            $dueClass = '';
                            if ($task->due_at) {
                                if ($task->isOverdue()) $dueClass = 'task-row__due--overdue';
                                elseif ($task->due_at->isToday()) $dueClass = 'task-row__due--today';
                                elseif ($task->due_at->lte(\Carbon\Carbon::now()->addDays(2))) $dueClass = 'task-row__due--soon';
                            }
                            $isActive = $selectedTask && $selectedTask->id === $task->id;
                        @endphp
                        <div
                            class="task-row {{ $isCompleted ? 'is-completed' : '' }} {{ $isActive ? 'is-active' : '' }}"
                            data-priority="{{ $task->priority }}"
                            wire:click="selectTask({{ $task->id }})"
                            wire:key="task-{{ $task->id }}"
                        >
                            <span class="task-row__priority"></span>

                            <span class="task-row__check" wire:click.stop>
                                <x-ui.checkbox
                                    round
                                    :checked="$isCompleted"
                                    wire:click="toggleCompleted({{ $task->id }})"
                                />
                            </span>

                            <div class="task-row__body">
                                <div class="task-row__title">{{ $task->title }}</div>
                                <div class="task-row__meta">
                                    @if($task->project)
                                        <span class="hstack gap-1">
                                            <span class="sidebar__project-dot" style="background: {{ $task->project->color }}"></span>
                                            {{ $task->project->name }}
                                        </span>
                                    @endif

                                    @if($task->due_at)
                                        <span class="task-row__due {{ $dueClass }}">
                                            <x-ui.icon name="calendar" :size="13" />
                                            @if($task->due_at->isToday())
                                                Сегодня{{ $task->all_day ? '' : ', ' . $task->due_at->format('H:i') }}
                                            @elseif($task->due_at->isTomorrow())
                                                Завтра{{ $task->all_day ? '' : ', ' . $task->due_at->format('H:i') }}
                                            @elseif($task->due_at->isYesterday())
                                                Вчера{{ $task->all_day ? '' : ', ' . $task->due_at->format('H:i') }}
                                            @else
                                                {{ $task->all_day ? $task->due_at->locale('ru')->isoFormat('D MMM') : $task->due_at->locale('ru')->isoFormat('D MMM, HH:mm') }}
                                            @endif
                                        </span>
                                    @endif

                                    @if($task->estimated_minutes)
                                        <span class="hstack gap-1">
                                            <x-ui.icon name="clock" :size="13" />
                                            {{ $task->estimated_minutes }} мин
                                        </span>
                                    @endif

                                    @foreach($task->tags as $tag)
                                        <span class="tag-chip tag-chip--static" wire:key="tk-{{ $task->id }}-{{ $tag->id }}">
                                            @if($tag->icon)
                                                <span style="color: {{ $tag->color }};">
                                                    <x-ui.icon :name="$tag->displayIcon()" :size="12" />
                                                </span>
                                            @else
                                                <span class="tag-chip__dot" style="background: {{ $tag->color }}"></span>
                                            @endif
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach

                                    @if($task->is_pinned)
                                        <span class="hstack gap-1" style="color: var(--accent)">
                                            <x-ui.icon name="pin" :size="13" />
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="task-row__actions" wire:click.stop>
                                <x-ui.dropdown align="right">
                                    <x-slot:trigger>
                                        <button class="btn btn--ghost btn--icon btn--sm" type="button" aria-label="Действия">
                                            <x-ui.icon name="more-h" :size="16" />
                                        </button>
                                    </x-slot:trigger>

                                    <button class="dropdown__item" wire:click="selectTask({{ $task->id }})">
                                        <x-ui.icon name="edit" :size="16" />
                                        <span>Открыть</span>
                                    </button>
                                    <button class="dropdown__item" wire:click="toggleCompleted({{ $task->id }})">
                                        <x-ui.icon name="check-circle" :size="16" />
                                        <span>{{ $isCompleted ? 'Вернуть в работу' : 'Завершить' }}</span>
                                    </button>
                                    <div class="dropdown__separator"></div>
                                    <button
                                        class="dropdown__item dropdown__item--danger"
                                        wire:click="deleteTask({{ $task->id }})"
                                        wire:confirm="Удалить задачу «{{ $task->title }}»?"
                                    >
                                        <x-ui.icon name="trash" :size="16" />
                                        <span>Удалить</span>
                                    </button>
                                </x-ui.dropdown>
                            </div>
                        </div>
                    @empty
                        <div class="task-list__empty">
                            @if($search !== '')
                                <div class="task-list__empty-illustration">
                                    <x-ui.icon name="search" :size="22" />
                                </div>
                                <h3>Ничего не найдено</h3>
                                <p class="text-muted" style="margin-top: 6px;">
                                    По запросу «{{ $search }}» задач не найдено.
                                </p>
                            @else
                                <div class="task-list__empty-illustration">
                                    <x-ui.icon name="sparkles" :size="22" />
                                </div>
                                <h3>Пусто. Прекрасно.</h3>
                                <p class="text-muted" style="margin-top: 6px;">
                                    Добавьте задачу — мы поможем сфокусироваться.
                                </p>
                            @endif
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <div class="task-board__rail">
            @if($selectedTask)
                <div
                    class="task-board__scrim"
                    wire:click="clearSelection"
                    aria-hidden="true"
                ></div>
                <div
                    class="task-board__splitter"
                    role="separator"
                    aria-orientation="vertical"
                    aria-label="Изменить ширину панели задачи"
                    @mousedown.prevent="beginResize($event)"
                ></div>
                <aside class="task-board__detail" wire:key="task-detail-aside-{{ $selectedTask->id }}">
                    <livewire:workspace.task-detail :taskId="$selectedTask->id" :key="'task-detail-' . $selectedTask->id" />
                </aside>
            @endif
        </div>
    </div>
</div>
