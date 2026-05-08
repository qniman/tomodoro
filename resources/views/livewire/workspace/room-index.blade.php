<div class="ws ws--full">
    <div class="ws__header">
        <div class="ws__head-left">
            <h1 class="ws__title">Комнаты</h1>
            <span class="ws__subtitle">Тихое место для работы вдвоём</span>
        </div>
        <div class="ws__head-right">
            <x-ui.button variant="primary" icon="plus" wire:click="$set('showCreateModal', true)">
                Создать комнату
            </x-ui.button>
        </div>
    </div>

    <div class="ws__body">
        {{-- Присоединиться по коду --}}
        <div class="room-join-bar">
            <x-ui.icon name="key" :size="16" style="color: var(--text-muted);" />
            <input
                type="text"
                class="room-join-bar__input"
                placeholder="Введите код комнаты…"
                wire:model="joinCode"
                wire:keydown.enter="joinRoom"
                maxlength="8"
                autocomplete="off"
                spellcheck="false"
            />
            <x-ui.button variant="ghost" size="sm" wire:click="joinRoom">Войти</x-ui.button>
            @error('joinCode')
                <span class="field__error">{{ $message }}</span>
            @enderror
        </div>

        {{-- Список комнат --}}
        @if($myRooms->isEmpty())
            <div class="room-empty">
                <x-ui.icon name="users" :size="40" style="color: var(--text-subtle); margin-bottom: var(--s-3);" />
                <div class="room-empty__title">Нет активных комнат</div>
                <div class="room-empty__sub">Создайте комнату и пригласите коллегу или друга</div>
            </div>
        @else
            <div class="room-grid">
                @foreach($myRooms as $ws)
                    @php
                        $online = $ws->members->filter(fn($m) => $m->last_seen_at && $m->last_seen_at->gt(now()->subMinutes(2)))->count();
                        $focused = $ws->members->where('status', 'focus')->count();
                    @endphp
                    <a href="{{ route('workspace.room', $ws) }}" wire:navigate class="room-card">
                        <div class="room-card__header">
                            <span class="room-card__name">{{ $ws->name }}</span>
                            @if($ws->activeSession)
                                <span class="room-card__badge room-card__badge--active">
                                    <span class="room-card__pulse"></span>
                                    {{ $ws->activeSession->phaseLabel() }}
                                </span>
                            @endif
                        </div>
                        <div class="room-card__meta">
                            <span>
                                <x-ui.icon name="users" :size="13" />
                                {{ $ws->members->count() }} участн.
                            </span>
                            @if($focused > 0)
                                <span style="color: var(--accent);">
                                    <x-ui.icon name="tomato" :size="13" />
                                    {{ $focused }} в фокусе
                                </span>
                            @endif
                        </div>
                        <div class="room-card__avatars">
                            @foreach($ws->members->take(5) as $m)
                                <x-ui.avatar :name="$m->user->name" :src="$m->user->avatar_url" size="xs"
                                    style="{{ $m->status === 'focus' ? 'outline: 2px solid var(--accent); outline-offset: 1px;' : '' }}" />
                            @endforeach
                        </div>
                        <div class="room-card__code">
                            <x-ui.icon name="key" :size="12" />
                            {{ $ws->invite_code }}
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Модал создания --}}
    <x-ui.modal :show="$showCreateModal" size="sm"
                close-action="$set('showCreateModal', false)"
                title="Новая комната">
        <x-ui.input
            label="Название"
            wire:model="newRoomName"
            placeholder="Например: Проект X, Учёба по понедельникам…"
            autofocus
            maxlength="80"
        />
        @error('newRoomName')
            <p class="field__error">{{ $message }}</p>
        @enderror

        <x-slot:footer>
            <x-ui.button variant="ghost" wire:click="$set('showCreateModal', false)">Отмена</x-ui.button>
            <x-ui.button variant="primary" icon="check" wire:click="createRoom" wireTarget="createRoom">
                Создать
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
