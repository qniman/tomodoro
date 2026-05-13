<div class="kanban-index-page">
    <div class="workspace__header">
        <h1 class="workspace__title">Доски</h1>
        <div class="workspace__header-actions">
            <button
                type="button"
                class="btn btn--primary btn--sm"
                wire:click="$set('showCreateForm', true)"
            >
                <x-ui.icon name="plus" :size="16" />
                <span>Создать доску</span>
            </button>
        </div>
    </div>

    {{-- Create form --}}
    @if($showCreateForm)
        <div class="kanban-create-overlay" wire:click.self="$set('showCreateForm', false)">
            <div class="kanban-create-modal">
                <div class="kanban-create-modal__header">
                    <h2 class="kanban-create-modal__title">Новая доска</h2>
                    <button type="button" class="btn-icon" wire:click="$set('showCreateForm', false)">
                        <x-ui.icon name="x" :size="18" />
                    </button>
                </div>

                <form wire:submit="createBoard" class="kanban-create-modal__body">
                    <div class="form-group">
                        <label class="form-label">Название</label>
                        <input
                            type="text"
                            class="input"
                            wire:model="newBoardName"
                            placeholder="Мой проект"
                            autofocus
                        />
                        @error('newBoardName') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Цвет</label>
                        <div class="kanban-color-picker">
                            @foreach(['#6366f1','#e5533a','#2ea043','#cf8a04','#2563eb','#9333ea','#0891b2','#64748b'] as $c)
                                <button
                                    type="button"
                                    class="kanban-color-picker__swatch {{ $newBoardColor === $c ? 'is-active' : '' }}"
                                    style="background: {{ $c }}"
                                    wire:click="$set('newBoardColor', '{{ $c }}')"
                                    title="{{ $c }}"
                                ></button>
                            @endforeach
                        </div>
                    </div>

                    <div class="kanban-create-modal__footer">
                        <button type="button" class="btn btn--ghost btn--sm" wire:click="$set('showCreateForm', false)">Отмена</button>
                        <button type="submit" class="btn btn--primary btn--sm">Создать</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Boards grid --}}
    @if($ownBoards->isEmpty() && $sharedBoards->isEmpty())
        <div class="kanban-empty">
            <div class="kanban-empty__icon">
                <x-ui.icon name="layout-kanban" :size="48" />
            </div>
            <h2 class="kanban-empty__title">Нет досок</h2>
            <p class="kanban-empty__text">Создай первую доску и распредели задачи по колонкам</p>
            <button type="button" class="btn btn--primary" wire:click="$set('showCreateForm', true)">
                <x-ui.icon name="plus" :size="16" />
                Создать доску
            </button>
        </div>
    @else
        {{-- Own boards --}}
        @if($ownBoards->isNotEmpty())
            <div class="kanban-boards-grid">
                @foreach($ownBoards as $board)
                    @include('livewire.workspace.partials.kanban-board-card', ['board' => $board, 'isOwner' => true])
                @endforeach
            </div>
        @endif

        {{-- Shared boards --}}
        @if($sharedBoards->isNotEmpty())
            <div class="kanban-section-header">
                <x-ui.icon name="users" :size="15" />
                Общие доски
            </div>
            <div class="kanban-boards-grid">
                @foreach($sharedBoards as $board)
                    @include('livewire.workspace.partials.kanban-board-card', ['board' => $board, 'isOwner' => false])
                @endforeach
            </div>
        @endif
    @endif
</div>
