<div
    class="kanban-page"
    x-data="taskBoardSplit({ storageKey: 'tomodoro:kanbanDetailW', minWidth: 320, maxWidth: 700 })"
    @mousemove.window="onMove($event)"
    @mouseup.window="endResize()"
    @keydown.escape.window="if ($wire.selectedTaskId) $wire.clearSelection()"
>
    {{-- Header --}}
    <div class="workspace__header workspace__header--kanban">
        <div class="workspace__header-left">
            <a href="{{ route('app.kanban') }}" wire:navigate class="kanban-back-link">
                <x-ui.icon name="arrow-left" :size="16" />
            </a>
            <h1 class="workspace__title">{{ $board->name }}</h1>

            {{-- Members avatars --}}
            @if($board->members->isNotEmpty())
                <div class="kanban-members">
                    @foreach($board->members->take(5) as $member)
                        <span class="kanban-member-avatar" title="{{ $member->name }}">
                            {{ mb_strtoupper(mb_substr($member->name, 0, 1)) }}
                        </span>
                    @endforeach
                    @if($board->members->count() > 5)
                        <span class="kanban-member-avatar kanban-member-avatar--more">
                            +{{ $board->members->count() - 5 }}
                        </span>
                    @endif
                </div>
            @endif
        </div>

        <div class="workspace__header-actions">
            @if($isOwner)
                <button
                    type="button"
                    class="btn btn--ghost btn--sm {{ $showSharePanel ? 'is-active' : '' }}"
                    wire:click="toggleSharePanel"
                >
                    <x-ui.icon name="share" :size="15" />
                    Поделиться
                </button>
            @endif
            <button
                type="button"
                class="btn btn--ghost btn--sm"
                wire:click="$set('showAddColumn', true)"
                x-show="!$wire.showAddColumn"
            >
                <x-ui.icon name="plus" :size="16" />
                Добавить колонку
            </button>
        </div>
    </div>

    {{-- Share panel --}}
    @if($showSharePanel && $isOwner)
        <div class="kanban-share-panel">
            <div class="kanban-share-panel__inner">
                <h3 class="kanban-share-panel__title">Совместный доступ</h3>

                {{-- Invite form --}}
                <form wire:submit="inviteMember" class="kanban-share-panel__invite">
                    <input
                        type="email"
                        class="input input--sm"
                        wire:model="inviteEmail"
                        placeholder="Email пользователя"
                        autocomplete="off"
                    />
                    <button type="submit" class="btn btn--primary btn--sm">Добавить</button>
                </form>

                @if($inviteError)
                    <p class="kanban-share-panel__msg kanban-share-panel__msg--error">{{ $inviteError }}</p>
                @endif
                @if($inviteSuccess)
                    <p class="kanban-share-panel__msg kanban-share-panel__msg--success">{{ $inviteSuccess }}</p>
                @endif

                {{-- Members list --}}
                @if($board->members->isNotEmpty())
                    <ul class="kanban-share-panel__list">
                        @foreach($board->members as $member)
                            <li class="kanban-share-panel__member">
                                <span class="kanban-member-avatar">{{ mb_strtoupper(mb_substr($member->name, 0, 1)) }}</span>
                                <div class="kanban-share-panel__member-info">
                                    <span class="kanban-share-panel__member-name">{{ $member->name }}</span>
                                    <span class="kanban-share-panel__member-email">{{ $member->email }}</span>
                                </div>
                                <button
                                    type="button"
                                    class="btn-icon btn-icon--sm"
                                    wire:click="removeMember({{ $member->id }})"
                                    title="Удалить участника"
                                >
                                    <x-ui.icon name="x" :size="14" />
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="kanban-share-panel__empty">Нет участников. Добавьте коллег по email.</p>
                @endif
            </div>
        </div>
    @endif

    {{-- Main: columns + resizable detail --}}
    <div
        class="workspace__main workspace__main--task-board workspace__main--kanban"
        x-bind:style="taskBoardRailStyle()"
    >
        {{-- Columns area --}}
        <div
            class="kanban-columns-outer"
            x-data="kanbanBoard(@js($board->id))"
            x-init="initSortable()"
            x-on:livewire:navigated.window="initSortable()"
            x-on:kanban-refresh-sortable.window="$nextTick(() => initSortable())"
        >
            <div class="kanban-columns" id="kanban-columns-container">

                @foreach($board->columns as $column)
                    <div
                        class="kanban-col"
                        data-column-id="{{ $column->id }}"
                        wire:key="col-{{ $column->id }}"
                        @if($column->color) style="--col-color: {{ $column->color }}" @endif
                    >
                        {{-- Column header --}}
                        <div
                            class="kanban-col__header {{ $column->color ? 'kanban-col__header--colored' : '' }}"
                            @if($column->color) style="background: linear-gradient(180deg, {{ $column->color }}28 0%, transparent 100%)" @endif
                        >
                            @if($editingColumnId === $column->id)
                                <form wire:submit="saveColumnName" class="kanban-col__rename-form">
                                    <input
                                        type="text"
                                        class="kanban-col__rename-input"
                                        wire:model="editingColumnName"
                                        wire:blur="saveColumnName"
                                        @keydown.escape="$wire.cancelColumnRename()"
                                        x-init="$el.focus(); $el.select()"
                                    />
                                </form>
                            @else
                                <button
                                    type="button"
                                    class="kanban-col__title"
                                    wire:click="startRenameColumn({{ $column->id }})"
                                    title="Переименовать"
                                >{{ $column->name }}</button>
                            @endif

                            <div class="kanban-col__header-right">
                                <span class="kanban-col__count">{{ $column->tasks->count() }}</span>

                                <x-ui.dropdown align="right" direction="down" :width="160">
                                    <x-slot:trigger>
                                        <button type="button" class="btn-icon btn-icon--sm" title="Действия с колонкой">
                                            <x-ui.icon name="more-h" :size="15" />
                                        </button>
                                    </x-slot:trigger>
                                    <button type="button" class="dropdown__item" wire:click="startRenameColumn({{ $column->id }})">
                                        <x-ui.icon name="edit" :size="15" />
                                        <span>Переименовать</span>
                                    </button>
                                    <button type="button" class="dropdown__item" wire:click="startAddCard({{ $column->id }})">
                                        <x-ui.icon name="plus" :size="15" />
                                        <span>Добавить карточку</span>
                                    </button>
                                    <div class="dropdown__separator"></div>
                                    <div class="kanban-col-color-picker">
                                        <span class="kanban-col-color-picker__label">Цвет</span>
                                        <div class="kanban-col-color-picker__swatches">
                                            @foreach(['#6366f1','#e5533a','#2ea043','#cf8a04','#2563eb','#9333ea','#0891b2','#64748b'] as $c)
                                                <button
                                                    type="button"
                                                    class="kanban-col-color-picker__swatch {{ $column->color === $c ? 'is-active' : '' }}"
                                                    style="background: {{ $c }}"
                                                    wire:click="setColumnColor({{ $column->id }}, '{{ $c }}')"
                                                    title="{{ $c }}"
                                                ></button>
                                            @endforeach
                                            <button
                                                type="button"
                                                class="kanban-col-color-picker__swatch kanban-col-color-picker__swatch--clear {{ !$column->color ? 'is-active' : '' }}"
                                                wire:click="setColumnColor({{ $column->id }}, '')"
                                                title="Без цвета"
                                            ><x-ui.icon name="x" :size="10" /></button>
                                        </div>
                                    </div>
                                    <div class="dropdown__separator"></div>
                                    <button
                                        type="button"
                                        class="dropdown__item dropdown__item--danger"
                                        wire:click="deleteColumn({{ $column->id }})"
                                        wire:confirm="Удалить колонку «{{ $column->name }}»? Задачи вернутся во Входящие."
                                    >
                                        <x-ui.icon name="trash" :size="15" />
                                        <span>Удалить</span>
                                    </button>
                                </x-ui.dropdown>
                            </div>
                        </div>

                        {{-- Cards list --}}
                        <div
                            class="kanban-col__cards"
                            data-column-id="{{ $column->id }}"
                            id="kanban-cards-{{ $column->id }}"
                        >
                            @foreach($column->tasks as $task)
                                @php
                                    $dueClass = '';
                                    if ($task->due_at) {
                                        if ($task->isOverdue()) $dueClass = 'kanban-card__due--overdue';
                                        elseif ($task->due_at->isToday()) $dueClass = 'kanban-card__due--today';
                                        elseif ($task->due_at->lte(\Carbon\Carbon::now()->addDays(2))) $dueClass = 'kanban-card__due--soon';
                                    }
                                @endphp
                                <div
                                    class="kanban-card {{ $task->completed_at ? 'kanban-card--done' : '' }} {{ $selectedTaskId === $task->id ? 'kanban-card--selected' : '' }}"
                                    data-task-id="{{ $task->id }}"
                                    wire:key="card-{{ $task->id }}"
                                    data-priority="{{ $task->priority }}"
                                    @click="$wire.selectTask({{ $task->id }})"
                                >
                                    <span class="kanban-card__priority-stripe"></span>

                                    <div class="kanban-card__body">
                                        <div class="kanban-card__top">
                                            <button
                                                type="button"
                                                class="kanban-card__check {{ $task->completed_at ? 'is-done' : '' }}"
                                                wire:click.stop="toggleCompleted({{ $task->id }})"
                                                title="{{ $task->completed_at ? 'Отметить незавершённой' : 'Завершить задачу' }}"
                                            >
                                                @if($task->completed_at)
                                                    <x-ui.icon name="check-circle" :size="16" />
                                                @else
                                                    <x-ui.icon name="circle" :size="16" />
                                                @endif
                                            </button>

                                            <span class="kanban-card__title">{{ $task->title }}</span>
                                        </div>

                                        @if($task->tags->isNotEmpty() || $task->due_at)
                                            <div class="kanban-card__footer">
                                                @if($task->due_at)
                                                    <span class="kanban-card__due {{ $dueClass }}">
                                                        <x-ui.icon name="calendar" :size="12" />
                                                        @if($task->due_at->isToday())
                                                            Сегодня
                                                        @elseif($task->due_at->isTomorrow())
                                                            Завтра
                                                        @else
                                                            {{ $task->due_at->locale('ru')->isoFormat('D MMM') }}
                                                        @endif
                                                    </span>
                                                @endif

                                                @if($task->tags->isNotEmpty())
                                                    <div class="kanban-card__tags">
                                                        @foreach($task->tags->take(3) as $tag)
                                                            <span
                                                                class="kanban-tag"
                                                                style="background: {{ $tag->color }}20; color: {{ $tag->color }}"
                                                                wire:key="ctag-{{ $task->id }}-{{ $tag->id }}"
                                                            >{{ $tag->name }}</span>
                                                        @endforeach
                                                        @if($task->tags->count() > 3)
                                                            <span class="kanban-tag kanban-tag--more">+{{ $task->tags->count() - 3 }}</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Add card form --}}
                        @if($addingCardToColumn === $column->id)
                            <form wire:submit="addCard" class="kanban-add-card-form" @click.stop>
                                <textarea
                                    class="kanban-add-card-form__input"
                                    wire:model="newCardTitle"
                                    placeholder="Название задачи…"
                                    rows="2"
                                    @keydown.enter.prevent="$wire.addCard()"
                                    @keydown.escape="$wire.cancelAddCard()"
                                    x-init="$el.focus()"
                                ></textarea>
                                @error('newCardTitle')
                                    <span class="form-error" style="padding: 0 var(--s-2) var(--s-1)">{{ $message }}</span>
                                @enderror
                                <div class="kanban-add-card-form__actions">
                                    <button type="submit" class="btn btn--primary btn--sm">Добавить</button>
                                    <button type="button" class="btn-icon" wire:click="cancelAddCard">
                                        <x-ui.icon name="x" :size="16" />
                                    </button>
                                </div>
                            </form>
                        @else
                            <button
                                type="button"
                                class="kanban-col__add-card"
                                wire:click="startAddCard({{ $column->id }})"
                            >
                                <x-ui.icon name="plus" :size="15" />
                                Добавить карточку
                            </button>
                        @endif
                    </div>
                @endforeach

                {{-- Add column --}}
                @if($showAddColumn)
                    <div class="kanban-col kanban-col--new">
                        <form wire:submit="addColumn" class="kanban-add-col-form">
                            <input
                                type="text"
                                class="input kanban-add-col-form__input"
                                wire:model="newColumnName"
                                placeholder="Название колонки"
                                @keydown.escape="$wire.set('showAddColumn', false)"
                                x-init="$el.focus()"
                            />
                            @error('newColumnName') <span class="form-error">{{ $message }}</span> @enderror
                            <div class="kanban-add-col-form__actions">
                                <button type="submit" class="btn btn--primary btn--sm">Добавить</button>
                                <button type="button" class="btn-icon" wire:click="$set('showAddColumn', false)">
                                    <x-ui.icon name="x" :size="16" />
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    <button
                        type="button"
                        class="kanban-add-col-btn"
                        wire:click="$set('showAddColumn', true)"
                    >
                        <x-ui.icon name="plus" :size="16" />
                        Добавить колонку
                    </button>
                @endif
            </div>
        </div>

        {{-- Resizable task detail rail --}}
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
                <aside class="task-board__detail" wire:key="kanban-detail-{{ $selectedTask->id }}">
                    <livewire:workspace.task-detail
                        :taskId="$selectedTask->id"
                        :key="'kanban-task-' . $selectedTask->id"
                    />
                </aside>
            @endif
        </div>
    </div>
</div>

@script
<script>
Alpine.data('kanbanBoard', (boardId) => ({
    sortableInstances: [],
    currentUserId: @js(Auth::id()),

    init() {
        this.initSortable();
        this.initEchoListener();
    },

    initSortable() {
        if (typeof Sortable === 'undefined') return;

        this.sortableInstances.forEach(s => s.destroy());
        this.sortableInstances = [];

        const colContainer = document.getElementById('kanban-columns-container');
        if (colContainer) {
            this.sortableInstances.push(Sortable.create(colContainer, {
                animation: 150,
                handle: '.kanban-col__header',
                draggable: '.kanban-col:not(.kanban-col--new)',
                ghostClass: 'kanban-col--ghost',
                onEnd: () => {
                    const ids = [...colContainer.querySelectorAll('.kanban-col[data-column-id]')]
                        .map(el => parseInt(el.dataset.columnId));
                    $wire.reorderColumns(ids);
                },
            }));
        }

        document.querySelectorAll('.kanban-col__cards').forEach(list => {
            this.sortableInstances.push(Sortable.create(list, {
                group: 'kanban-cards-' + boardId,
                animation: 150,
                ghostClass: 'kanban-card--ghost',
                dragClass: 'kanban-card--dragging',
                onEnd: (evt) => {
                    const taskId = parseInt(evt.item.dataset.taskId);
                    const columnId = parseInt(evt.to.dataset.columnId);
                    const orderedIds = [...evt.to.querySelectorAll('[data-task-id]')]
                        .map(el => parseInt(el.dataset.taskId));
                    $wire.moveCard(taskId, columnId, orderedIds);
                },
            }));
        });
    },

    initEchoListener() {
        if (!window.Echo) return;

        window.Echo.private(`kanban.board.${boardId}`)
            .listen('.board.updated', (data) => {
                if (data.removedUserId && data.removedUserId === this.currentUserId) {
                    // Current user was kicked — redirect immediately
                    window.location.href = @js(route('app.kanban'));
                    return;
                }
                if (data.triggeredBy !== this.currentUserId) {
                    $wire.$refresh();
                }
            });
    },
}));
</script>
@endscript
