@php
    $myStatus = $myMember?->status ?? 'away';
    $isOwner  = $workspace->owner_id === auth()->id();

    // Данные сессии для JS
    $sessionData = null;
    if ($session) {
        $sessionData = [
            'id'               => $session->id,
            'phase'            => $session->phase,
            'duration_seconds' => $session->duration_seconds,
            'started_at_ms'    => $session->started_at->getTimestampMs(),
            'paused_at_ms'     => $session->paused_at?->getTimestampMs(),
            'remaining'        => $session->remainingSeconds(),
        ];
    }
@endphp

<div
    class="ws ws--full room-page"
    x-data="roomPage({
        workspaceId: {{ $workspace->id }},
        myUserId: {{ auth()->id() }},
        session: @js($sessionData),
        isOwner: {{ $isOwner ? 'true' : 'false' }},
        initialStatuses:   @js($members->pluck('status', 'user_id')),
        initialPomodoros:  @js($members->pluck('pomodoros_today', 'user_id')),
    })"
    x-init="init()"
    @room-timer-updated.window="onTimerUpdated($event.detail)"
    @room-chat-message.window="onChatMessage($event.detail)"
    @room-reaction.window="onReaction($event.detail)"
    @room-member-status.window="onMemberStatus($event.detail)"
>
    {{-- HEADER --}}
    <div class="ws__header">
        <div class="ws__head-left">
            <a href="{{ route('workspace.index') }}" wire:navigate class="btn btn--ghost btn--icon btn--sm" style="margin-right: var(--s-2);">
                <x-ui.icon name="chevron-left" :size="16" />
            </a>
            <div class="room-header-title">
                <h1 class="ws__title">{{ $workspace->name }}</h1>
                <span class="room-header-code">
                    <x-ui.icon name="key" :size="12" />
                    {{ $workspace->invite_code }}
                    <button
                        type="button"
                        class="room-copy-code"
                        x-data
                        @click="navigator.clipboard?.writeText('{{ $workspace->invite_code }}'); $dispatch('toast', {type:'success', title:'Код скопирован'})"
                        title="Скопировать код"
                    >
                        <x-ui.icon name="copy" :size="12" />
                    </button>
                </span>
            </div>
        </div>
        <div class="ws__head-right">
            {{-- Мой статус --}}
            <div class="room-status-switcher">
                @foreach(['focus' => ['🍅', 'Фокус'], 'pause' => ['☕', 'Пауза'], 'away' => ['🌙', 'Отошёл']] as $s => [$icon, $label])
                    <button
                        type="button"
                        class="room-status-btn {{ $myStatus === $s ? 'is-active' : '' }}"
                        wire:click="setStatus('{{ $s }}')"
                        title="{{ $label }}"
                        data-status="{{ $s }}"
                    >{{ $icon }} {{ $label }}</button>
                @endforeach
            </div>

            <x-ui.dropdown align="right" :width="180">
                <x-slot:trigger>
                    <x-ui.button variant="ghost" icon="more-h" size="sm" />
                </x-slot:trigger>
                <button
                    type="button"
                    class="dropdown__item"
                    x-data
                    @click="
                        navigator.clipboard?.writeText('{{ url('/workspace') }}?join={{ $workspace->invite_code }}');
                        $dispatch('toast', {type: 'success', title: 'Ссылка скопирована', message: '{{ url('/workspace') }}?join={{ $workspace->invite_code }}'});
                    "
                >
                    <x-ui.icon name="share" :size="16" />
                    <span>Поделиться</span>
                </button>
                <div class="dropdown__separator"></div>
                <button type="button" class="dropdown__item dropdown__item--danger"
                        wire:click="leaveRoom" wire:confirm="Покинуть комнату?">
                    <x-ui.icon name="log-out" :size="16" />
                    <span>Покинуть</span>
                </button>
            </x-ui.dropdown>
        </div>
    </div>

    {{-- BODY --}}
    <div class="ws__body">
    <div class="room-layout">

        {{-- ===== Колонка: Участники ===== --}}
        <div class="room-members-col">
            <div class="room-section-title">
                <x-ui.icon name="users" :size="14" />
                Участники · {{ $members->count() }}
            </div>

            <div class="room-members-list">
                @foreach($members as $member)
                    <div class="room-member" data-user-id="{{ $member->user_id }}"
                         :class="memberStatuses[{{ $member->user_id }}] ? 'status-' + memberStatuses[{{ $member->user_id }}] : 'status-{{ $member->status }}'">
                        <x-ui.avatar :name="$member->user->name" :src="$member->user->avatar_url" size="md" />
                        <div class="room-member__info">
                            <span class="room-member__name">{{ $member->user->name }}
                                @if($member->user_id === $workspace->owner_id)
                                    <span class="room-member__owner-tag">👑</span>
                                @endif
                                @if($member->user_id === auth()->id())
                                    <span class="room-member__you-tag">ты</span>
                                @endif
                            </span>
                            <span class="room-member__status"
                                  x-text="({'focus':'🍅 В фокусе','pause':'☕ Пауза','away':'🌙 Отошёл'})[memberStatuses[{{ $member->user_id }}] ?? '{{ $member->status }}']">
                                {{ ['focus'=>'🍅 В фокусе','pause'=>'☕ Пауза','away'=>'🌙 Отошёл'][$member->status] ?? '🌙 Отошёл' }}
                            </span>
                        </div>
                        <div class="room-member__pomo" x-text="'🍅 ' + (memberPomodoros[{{ $member->user_id }}] ?? {{ $member->pomodoros_today }})">
                            🍅 {{ $member->pomodoros_today }}
                        </div>
                        {{-- Реакции на этого участника --}}
                        @if($member->user_id !== auth()->id())
                            <div class="room-reaction-btns">
                                @foreach(['👏', '🧘', '☕', '🔥', '💪'] as $emoji)
                                    <button type="button" class="room-reaction-btn"
                                            wire:click="sendReaction('{{ $emoji }}', {{ $member->user_id }})"
                                            title="{{ $emoji }}">{{ $emoji }}</button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ===== Центр: Таймер ===== --}}
        <div class="room-timer-col">
            <div class="room-section-title">
                <x-ui.icon name="timer" :size="14" />
                Общий таймер
            </div>

            <div class="room-timer-panel">
                @if($session)
                    <div class="room-timer-phase" :class="timerPhase === 'work' ? 'is-work' : 'is-break'">
                        <span x-text="timerPhase === 'work' ? '🍅 Фокус' : '☕ Перерыв'">
                            {{ $session->phaseLabel() }}
                        </span>
                        <span class="room-timer-started-by">
                            запустил {{ $session->startedBy->name }}
                        </span>
                    </div>

                    <div class="room-timer-clock" :class="timerPaused ? 'is-paused' : ''">
                        <svg class="room-timer-ring" viewBox="0 0 120 120">
                            <circle class="room-timer-ring__track" cx="60" cy="60" r="54" />
                            <circle class="room-timer-ring__progress" cx="60" cy="60" r="54"
                                    :stroke-dasharray="339.3"
                                    :stroke-dashoffset="339.3 * (1 - timerProgress)" />
                        </svg>
                        <div class="room-timer-clock__inner">
                            <div class="room-timer-clock__time" x-text="timerDisplay">
                                {{ gmdate('i:s', $session->remainingSeconds()) }}
                            </div>
                        </div>
                    </div>

                    <div class="room-timer-controls">
                        <template x-if="timerPaused">
                            <x-ui.button variant="primary" icon="play" wire:click="resumeTimer">Продолжить</x-ui.button>
                        </template>
                        <template x-if="!timerPaused">
                            <x-ui.button variant="ghost" icon="pause" wire:click="pauseTimer">Пауза</x-ui.button>
                        </template>
                        <x-ui.button variant="ghost" icon="stop" wire:click="stopTimer" wire:confirm="Остановить таймер для всех?">Стоп</x-ui.button>
                    </div>
                @else
                    <div class="room-timer-idle">
                        <div class="room-timer-idle__icon">⏱</div>
                        <div class="room-timer-idle__title">Таймер не запущен</div>
                        <div class="room-timer-idle__sub">Запусти общую сессию фокуса</div>
                        <x-ui.button variant="primary" icon="play" wire:click="openTimerModal">
                            Запустить таймер
                        </x-ui.button>
                    </div>
                @endif
            </div>

            {{-- Реакции-анонсы (floating) --}}
            <div class="room-reactions-feed" x-show="reactions.length > 0">
                <template x-for="r in reactions" :key="r.id">
                    <div class="room-reaction-toast" x-transition.opacity.duration.300ms>
                        <span x-text="r.from_name" class="room-reaction-toast__name"></span>
                        <span x-text="r.emoji" class="room-reaction-toast__emoji"></span>
                        <template x-if="r.to_user_id">
                            <span x-text="'→ ' + (r.to_name ?? '')" class="room-reaction-toast__to"></span>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        {{-- ===== Чат ===== --}}
        <div class="room-chat-col">
            <div class="room-section-title">
                <x-ui.icon name="message" :size="14" />
                Чат · только здесь и сейчас
            </div>

            <div class="room-chat-messages" x-ref="chatMessages">
                @forelse($messages as $msg)
                    <div class="room-chat-msg {{ $msg->user_id === auth()->id() ? 'is-mine' : '' }}">
                        <x-ui.avatar :name="$msg->user->name" :src="$msg->user->avatar_url" size="xs" />
                        <div class="room-chat-msg__bubble">
                            <span class="room-chat-msg__name">{{ $msg->user->name }}</span>
                            <span class="room-chat-msg__text">{{ $msg->body }}</span>
                        </div>
                    </div>
                @empty
                    <div class="room-chat-empty">Начните разговор…</div>
                @endforelse

                {{-- Новые сообщения из Echo --}}
                <template x-for="msg in chatMessages" :key="msg.id">
                    <div class="room-chat-msg" :class="msg.user_id === {{ auth()->id() }} ? 'is-mine' : ''">
                        <div class="room-chat-msg__avatar">
                            <template x-if="msg.user_avatar">
                                <img :src="msg.user_avatar" :alt="msg.user_name" class="avatar avatar--xs" />
                            </template>
                            <template x-if="!msg.user_avatar">
                                <div class="avatar avatar--xs avatar--initials"
                                     x-text="msg.user_name.charAt(0).toUpperCase()"></div>
                            </template>
                        </div>
                        <div class="room-chat-msg__bubble">
                            <span class="room-chat-msg__name" x-text="msg.user_name"></span>
                            <span class="room-chat-msg__text" x-text="msg.body"></span>
                        </div>
                    </div>
                </template>
            </div>

            <form class="room-chat-form" wire:submit.prevent="sendMessage" @submit="$nextTick(() => scrollChat())">
                <input
                    type="text"
                    class="room-chat-input"
                    placeholder="Сообщение…"
                    wire:model="chatInput"
                    maxlength="500"
                    autocomplete="off"
                />
                <button type="submit" class="room-chat-send">
                    <x-ui.icon name="send" :size="16" />
                </button>
            </form>
        </div>

    </div>{{-- /room-layout --}}
    </div>{{-- /ws__body --}}

    {{-- Модал настройки таймера --}}
    <x-ui.modal :show="$showTimerModal" size="sm"
                close-action="$set('showTimerModal', false)"
                title="Запустить общий таймер">
        <div class="vstack gap-4">
            <div class="field">
                <label class="field__label">Работа (мин)</label>
                <input type="range" min="5" max="60" step="5" wire:model.live="timerWorkMinutes" class="range-input" />
                <div class="field__hint" style="text-align: center; font-family: var(--font-mono); font-size: var(--fz-lg);">
                    {{ $timerWorkMinutes }} мин
                </div>
            </div>
            <div class="field">
                <label class="field__label">Перерыв (мин)</label>
                <input type="range" min="1" max="30" step="1" wire:model.live="timerBreakMinutes" class="range-input" />
                <div class="field__hint" style="text-align: center; font-family: var(--font-mono); font-size: var(--fz-lg);">
                    {{ $timerBreakMinutes }} мин
                </div>
            </div>
        </div>

        <x-slot:footer>
            <x-ui.button variant="ghost" wire:click="$set('showTimerModal', false)">Отмена</x-ui.button>
            <x-ui.button variant="primary" icon="play" wire:click="startTimer" wireTarget="startTimer">
                Запустить для всех
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

</div>

@push('scripts')
<script>
window.roomPage = function({ workspaceId, myUserId, session, isOwner, initialStatuses = {}, initialPomodoros = {} }) {
    return {
        workspaceId,
        myUserId,
        isOwner,

        // Таймер
        session,
        timerNow: Date.now(),
        timerHandle: null,

        // Реактивное состояние участников (инициализируем серверными данными)
        memberStatuses: Object.assign({}, initialStatuses),
        memberPomodoros: Object.assign({}, initialPomodoros),

        // Чат (новые, пришедшие через Echo)
        chatMessages: [],

        // Реакции (эфемерные)
        reactions: [],

        get timerRemaining() {
            if (!this.session) return 0;
            if (this.session.paused_at_ms) {
                return Math.max(0, this.session.duration_seconds - Math.floor((this.session.paused_at_ms - this.session.started_at_ms) / 1000));
            }
            return Math.max(0, this.session.duration_seconds - Math.floor((this.timerNow - this.session.started_at_ms) / 1000));
        },

        get timerProgress() {
            if (!this.session || this.session.duration_seconds <= 0) return 0;
            return Math.max(0, Math.min(1, 1 - this.timerRemaining / this.session.duration_seconds));
        },

        get timerDisplay() {
            const s = this.timerRemaining;
            return String(Math.floor(s / 60)).padStart(2,'0') + ':' + String(s % 60).padStart(2,'0');
        },

        get timerPhase() { return this.session?.phase ?? 'work'; },
        get timerPaused() { return !!this.session?.paused_at_ms; },

        init() {
            this.startTick();
            this.listenEcho();
        },

        startTick() {
            const tick = () => {
                this.timerNow = Date.now();
                // Уведомляем сервер, когда таймер истёк
                if (this.session && !this.session.paused_at_ms && this.timerRemaining === 0) {
                    this.$wire.timerFinished(this.session.id);
                    this.session = null;
                }
                this.timerHandle = requestAnimationFrame(tick);
            };
            this.timerHandle = requestAnimationFrame(tick);
        },

        listenEcho() {
            if (!window.Echo) return;
            window.Echo.private(`workspace.${this.workspaceId}`)
                .listen('.timer.updated', (e) => window.dispatchEvent(new CustomEvent('room-timer-updated', { detail: e })))
                .listen('.chat.message',  (e) => window.dispatchEvent(new CustomEvent('room-chat-message',  { detail: e })))
                .listen('.reaction.sent', (e) => window.dispatchEvent(new CustomEvent('room-reaction',      { detail: e })))
                .listen('.member.status', (e) => window.dispatchEvent(new CustomEvent('room-member-status', { detail: e })));
        },

        onTimerUpdated(e) {
            this.session = e.session;
            this.timerNow = Date.now();
            if (e.action === 'finished') {
                this.$dispatch('toast', { type: 'success', title: e.session?.phase === 'break' ? '☕ Перерыв!' : '🍅 Снова в работу!' });
            }
        },

        onChatMessage(msg) {
            this.chatMessages.push(msg);
            this.$nextTick(() => this.scrollChat());
        },

        onReaction(r) {
            this.reactions.push(r);
            setTimeout(() => {
                this.reactions = this.reactions.filter(x => x.id !== r.id);
            }, 4000);
        },

        onMemberStatus(data) {
            this.memberStatuses[data.user_id] = data.status;
            this.memberPomodoros[data.user_id] = data.pomodoros_today;
        },

        scrollChat() {
            const el = this.$refs.chatMessages;
            if (el) el.scrollTop = el.scrollHeight;
        },
    };
};
</script>
@endpush
