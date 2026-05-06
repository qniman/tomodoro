<div>
    <header class="workspace__header">
        <div class="workspace__title">
            <span>{{ $scopeLabel }}</span>
            @if($scopeMeta)
                <span class="workspace__title-meta">· {{ $scopeMeta }}</span>
            @endif
            <span class="badge" style="margin-left: var(--s-2)">
                {{ $tasks->count() }} {{ trans_choice('задача|задачи|задач', $tasks->count()) }}
            </span>
        </div>

        <div class="hstack gap-2">
            <div class="input-group" style="height: 36px; min-width: 240px;">
                <span class="input-group__addon"><x-ui.icon name="search" :size="16" /></span>
                <input
                    type="search"
                    class="input"
                    placeholder="Поиск задачи…"
                    wire:model.live.debounce.300ms="search"
                />
            </div>

            <x-ui.button variant="ghost" icon="{{ $showCompleted ? 'eye' : 'eye-off' }}" size="sm" wire:click="$toggle('showCompleted')">
                {{ $showCompleted ? 'Скрыть завершённые' : 'Показать завершённые' }}
            </x-ui.button>

            <x-ui.button variant="primary" icon="plus" wire:click="openQuickAdd">
                Новая задача
            </x-ui.button>
        </div>
    </header>

    <div class="workspace__main" style="display: grid; grid-template-columns: minmax(0, 1fr) {{ $selectedTask ? '420px' : '0' }}; gap: var(--s-5); align-items: start; transition: grid-template-columns var(--dur) var(--ease);">
        <section class="vstack gap-4" style="min-width: 0;">

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
                                            Сегодня, {{ $task->due_at->format('H:i') }}
                                        @elseif($task->due_at->isTomorrow())
                                            Завтра, {{ $task->due_at->format('H:i') }}
                                        @elseif($task->due_at->isYesterday())
                                            Вчера, {{ $task->due_at->format('H:i') }}
                                        @else
                                            {{ $task->due_at->locale('ru')->isoFormat('D MMM, HH:mm') }}
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
                                    <span class="tag-chip">
                                        <span class="tag-chip__dot" style="background: {{ $tag->color }}"></span>
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
                        <div class="task-list__empty-illustration">
                            <x-ui.icon name="sparkles" :size="22" />
                        </div>
                        <h3>Пусто. Прекрасно.</h3>
                        <p class="text-muted" style="margin-top: 6px;">
                            Добавьте задачу — мы поможем сфокусироваться.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>

        @if($selectedTask)
            <aside style="position: sticky; top: var(--s-5); height: calc(100vh - 64px - var(--s-7));" wire:key="task-detail-{{ $selectedTask->id }}">
                <livewire:workspace.task-detail :taskId="$selectedTask->id" :key="'task-detail-' . $selectedTask->id" />
            </aside>
        @endif
    </div>
</div>
