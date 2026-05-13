<div class="kanban-board-card" wire:key="board-{{ $board->id }}">
    <div class="kanban-board-card__header" style="background: {{ $board->color }}">
        <span class="kanban-board-card__name">
            @if($isOwner && $renamingBoardId === $board->id)
                <form wire:submit="saveRename" class="kanban-board-card__rename-form">
                    <input
                        type="text"
                        class="kanban-board-card__rename-input"
                        wire:model="renamingBoardName"
                        wire:blur="saveRename"
                        @keydown.escape="$wire.cancelRename()"
                        x-init="$el.focus(); $el.select()"
                    />
                </form>
            @else
                {{ $board->name }}
            @endif
        </span>
        <div class="kanban-board-card__actions">
            <x-ui.dropdown align="right" direction="down" :width="168">
                <x-slot:trigger>
                    <button type="button" class="btn-icon btn-icon--sm btn-icon--on-color" title="Действия">
                        <x-ui.icon name="more-h" :size="16" />
                    </button>
                </x-slot:trigger>

                @if($isOwner)
                    <button type="button" class="dropdown__item" wire:click="startRename({{ $board->id }})">
                        <x-ui.icon name="edit" :size="16" />
                        <span>Переименовать</span>
                    </button>
                    <div class="dropdown__separator"></div>
                    <div class="kanban-col-color-picker">
                        <span class="kanban-col-color-picker__label">Цвет доски</span>
                        <div class="kanban-col-color-picker__swatches">
                            @foreach(['#6366f1','#e5533a','#2ea043','#cf8a04','#2563eb','#9333ea','#0891b2','#64748b'] as $c)
                                <button
                                    type="button"
                                    class="kanban-col-color-picker__swatch {{ $board->color === $c ? 'is-active' : '' }}"
                                    style="background: {{ $c }}"
                                    wire:click="setBoardColor({{ $board->id }}, '{{ $c }}')"
                                    title="{{ $c }}"
                                ></button>
                            @endforeach
                        </div>
                    </div>
                    <div class="dropdown__separator"></div>
                    <button
                        type="button"
                        class="dropdown__item dropdown__item--danger"
                        wire:click="deleteBoard({{ $board->id }})"
                        wire:confirm="Удалить доску «{{ $board->name }}»? Задачи вернутся во Входящие."
                    >
                        <x-ui.icon name="trash" :size="16" />
                        <span>Удалить</span>
                    </button>
                @else
                    <div class="kanban-board-card__shared-by">
                        Владелец: {{ $board->user->name }}
                    </div>
                    <div class="dropdown__separator"></div>
                    <button
                        type="button"
                        class="dropdown__item dropdown__item--danger"
                        wire:click="leaveBoard({{ $board->id }})"
                        wire:confirm="Покинуть доску «{{ $board->name }}»?"
                    >
                        <x-ui.icon name="log-out" :size="16" />
                        <span>Покинуть доску</span>
                    </button>
                @endif
            </x-ui.dropdown>
        </div>
    </div>
    <div class="kanban-board-card__body">
        @php
            $colN = $board->columns_count;
            $colWord = ($colN % 10 === 1 && $colN % 100 !== 11) ? 'колонка' : (($colN % 10 >= 2 && $colN % 10 <= 4 && ($colN % 100 < 10 || $colN % 100 >= 20)) ? 'колонки' : 'колонок');
            $tskN = $board->tasks_count;
            $tskWord = ($tskN % 10 === 1 && $tskN % 100 !== 11) ? 'задача' : (($tskN % 10 >= 2 && $tskN % 10 <= 4 && ($tskN % 100 < 10 || $tskN % 100 >= 20)) ? 'задачи' : 'задач');
        @endphp
        <div class="kanban-board-card__meta">
            <span class="kanban-board-card__stat">
                <x-ui.icon name="columns" :size="14" />
                {{ $colN }} {{ $colWord }}
            </span>
            <span class="kanban-board-card__stat">
                <x-ui.icon name="check-square" :size="14" />
                {{ $tskN }} {{ $tskWord }}
            </span>
        </div>
        <a
            href="{{ route('app.kanban.board', $board) }}"
            wire:navigate
            class="btn btn--secondary btn--sm kanban-board-card__open"
        >
            Открыть доску
            <x-ui.icon name="arrow-right" :size="14" />
        </a>
    </div>
</div>
