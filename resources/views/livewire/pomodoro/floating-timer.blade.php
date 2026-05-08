@php
    $hasSession = (bool) $session;

    $toMs = fn ($carbon) => $carbon ? (int) ($carbon->getTimestamp() * 1000) : null;

    $initial = [
        'phase' => $session?->phase ?? 'work',
        // snake_case: в DOM/JSON меньше сюрпризов со стилем ключей при @js()
        'phase_duration' => $session?->phaseDuration() ?? 1500,
        'phase_started_at_ms' => $toMs($session?->phase_started_at),
        'paused_at_ms' => $toMs($session?->paused_at),
        'completed' => $session?->completed_pomodoros ?? 0,
        'total' => $session?->total_pomodoros ?? 0,
    ];

    // Ring geometries
    $bigCx = 90; $bigR = 80;
    $bigC = 2 * pi() * $bigR;
    $smallCx = 32; $smallR = 28;
    $smallC = 2 * pi() * $smallR;

    $phase = $session?->phase ?? 'work';
@endphp

@if(! $visible)
    <div></div>
@else
    <div
        wire:key="pomo-{{ $session?->id ?? 'idle' }}-{{ $showLauncher ? 'l' : ($expanded ? 'e' : 'b') }}"
        class="pomo pomo-mount"
        data-phase="{{ $phase }}"
        data-pomo-phase="{{ $initial['phase'] }}"
        data-pomo-duration="{{ $initial['phase_duration'] }}"
        data-pomo-phase-started-ms="{{ $initial['phase_started_at_ms'] ?? '' }}"
        data-pomo-paused-ms="{{ $initial['paused_at_ms'] ?? '' }}"
        data-pomo-completed="{{ $initial['completed'] }}"
        data-pomo-total="{{ $initial['total'] }}"
    >
        <div class="pomo-shell">
        @if($showLauncher)
            {{-- ==== Launcher ==== --}}
            <div class="pomo-launcher">
                <div class="pomo-launcher__header">
                    <span class="hstack gap-2" style="color: var(--accent);">
                        <x-ui.icon name="timer" :size="18" />
                        <span class="pomo-launcher__title">Запустить помодоро</span>
                    </span>
                    <button type="button" class="btn btn--ghost btn--icon btn--sm" wire:click="closeLauncher" aria-label="Закрыть">
                        <x-ui.icon name="x" :size="16" />
                    </button>
                </div>

                <div class="pomo-launcher__body">
                    <div class="field">
                        <label class="field__label">Задача</label>
                        <x-ui.menu-select
                            property="launcherTaskId"
                            :value="$launcherTaskId"
                            :options="$launcherTaskOptions"
                            placeholder="Свободный фокус"
                            minWidth="320"
                            align="left"
                        />
                    </div>

                    <div class="pomo-launcher__plan">
                        <span class="pomo-launcher__plan-num">{{ $launcherPomodoros }}</span>
                        <div>
                            @php
                                $hint = match($launcherPlanSource) {
                                    'estimate' => 'по оценке задачи',
                                    'deadline' => 'до дедлайна',
                                    'fallback' => 'без оценки — старт по умолчанию',
                                    default => 'свободная сессия',
                                };
                                $minutes = $launcherPomodoros * $launcherWorkMinutes;
                            @endphp
                            <div style="font-weight: var(--fw-semibold);">
                                {{ trans_choice('помодоро|помодоро|помодоро', $launcherPomodoros) }}
                                · {{ $minutes }} мин чистой работы
                            </div>
                            <div style="opacity: .8; font-size: var(--fz-xs);">{{ $hint }}</div>
                        </div>
                    </div>

                    <div class="pomo-launcher__steppers">
                        @php
                            $steppers = [
                                ['key' => 'launcherPomodoros',   'value' => $launcherPomodoros,   'label' => 'Помодоро', 'min' => 1, 'max' => 24, 'step' => 1],
                                ['key' => 'launcherWorkMinutes', 'value' => $launcherWorkMinutes, 'label' => 'Работа',   'min' => 5, 'max' => 90, 'step' => 5],
                                ['key' => 'launcherShortBreak',  'value' => $launcherShortBreak,  'label' => 'Перерыв',  'min' => 1, 'max' => 30, 'step' => 1],
                            ];
                        @endphp
                        @foreach($steppers as $s)
                            <div class="stepper">
                                <div class="stepper__label">{{ $s['label'] }}</div>
                                <div class="stepper__value">{{ $s['value'] }}</div>
                                <div class="stepper__controls">
                                    <button class="stepper__btn" type="button" wire:click="$set('{{ $s['key'] }}', {{ max($s['min'], $s['value'] - $s['step']) }})">
                                        <x-ui.icon name="minus" :size="12" />
                                    </button>
                                    <button class="stepper__btn" type="button" wire:click="$set('{{ $s['key'] }}', {{ min($s['max'], $s['value'] + $s['step']) }})">
                                        <x-ui.icon name="plus" :size="12" />
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="pomo-launcher__footer">
                    <x-ui.button variant="ghost" wire:click="closeLauncher">Отмена</x-ui.button>
                    <x-ui.button variant="primary" icon="play" wire:click="startSession" wireTarget="startSession">
                        Запустить
                    </x-ui.button>
                </div>
            </div>

        @elseif($hasSession && $expanded)
            {{-- ==== Развернутая карточка ==== --}}
            <div class="pomo-card" data-phase="{{ $phase }}">
                <div class="pomo-card__handle">
                    <span class="pomo-card__phase">
                        <x-ui.icon name="{{ $session->isWorking() ? 'tomato' : 'sun-medium' }}" :size="14" />
                        <span>
                            {{ match ($session->phase ?? 'work') {
                                'short_break' => 'Короткий перерыв',
                                'long_break' => 'Длинный перерыв',
                                default => 'Фокус',
                            } }}
                        </span>
                    </span>
                    <div class="pomo-card__actions">
                        <button type="button" class="btn btn--ghost btn--icon btn--sm" wire:click="toggleExpand" aria-label="Свернуть">
                            <x-ui.icon name="minus" :size="14" />
                        </button>
                        <button type="button" class="btn btn--ghost btn--icon btn--sm" wire:click="hide" aria-label="Скрыть">
                            <x-ui.icon name="x" :size="14" />
                        </button>
                    </div>
                </div>

                <div class="pomo-card__body">
                    <div class="pomo-card__task">
                        @if($session->task)
                            <strong>{{ $session->task->title }}</strong>
                        @else
                            Свободный фокус
                        @endif
                    </div>

                    <div
                        class="pomo-live"
                        wire:ignore
                        x-data="pomoWidget(@js($initial))"
                        x-init="init()"
                    >
                    <div class="pomo-clock">
                        <svg viewBox="0 0 {{ 2 * $bigCx }} {{ 2 * $bigCx }}">
                            <circle class="pomo-clock__track" cx="{{ $bigCx }}" cy="{{ $bigCx }}" r="{{ $bigR }}" />
                            <circle
                                class="pomo-clock__progress"
                                cx="{{ $bigCx }}"
                                cy="{{ $bigCx }}"
                                r="{{ $bigR }}"
                                :stroke-dasharray="{{ $bigC }}"
                                :stroke-dashoffset="{{ $bigC }} * (1 - progressFraction)"
                            />
                        </svg>
                        <div>
                            <div class="pomo-clock__time" x-text="formattedTime">{{ gmdate('i:s', $session->phaseDuration()) }}</div>
                            <div class="pomo-clock__sub">
                                <span x-text="completed + ' / ' + total">{{ $session->completed_pomodoros }} / {{ $session->total_pomodoros }}</span>
                            </div>
                        </div>
                    </div>
                    </div>

                    <div class="pomo-card__counter">
                        @for($i = 0; $i < min($session->total_pomodoros, 12); $i++)
                            <span class="pomo-card__counter-dot {{ $i < $session->completed_pomodoros ? 'is-done' : '' }}"></span>
                        @endfor
                    </div>
                </div>

                <div class="pomo-card__controls">
                    <button class="pomo-card__small" type="button" wire:click="stop" wire:confirm="Завершить сессию?" aria-label="Остановить">
                        <x-ui.icon name="stop" :size="14" />
                    </button>

                    @if($session->paused_at)
                        <button class="pomo-card__big" type="button" aria-label="Продолжить" wire:click="resume">
                            <x-ui.icon name="play" :size="22" />
                        </button>
                    @else
                        <button class="pomo-card__big" type="button" aria-label="Пауза" wire:click="pause">
                            <x-ui.icon name="pause" :size="22" />
                        </button>
                    @endif

                    <button class="pomo-card__small" type="button" aria-label="Пропустить фазу" wire:click="skip">
                        <x-ui.icon name="fast-fwd" :size="14" />
                    </button>
                </div>
            </div>

        @elseif($hasSession)
            {{-- ==== Bubble (свёрнутый) ==== --}}
            <button type="button"
                    class="pomo-bubble"
                    data-phase="{{ $phase }}"
                    wire:click="toggleExpand"
                    aria-label="Открыть таймер">
                <span
                    class="pomo-live pomo-live--bubble"
                    wire:ignore
                    x-data="pomoWidget(@js($initial))"
                    x-init="init()"
                >
                <svg class="pomo-bubble__ring" viewBox="0 0 {{ 2 * $smallCx }} {{ 2 * $smallCx }}">
                    <circle class="pomo-bubble__ring-track" cx="{{ $smallCx }}" cy="{{ $smallCx }}" r="{{ $smallR }}" />
                    <circle class="pomo-bubble__ring-progress"
                            cx="{{ $smallCx }}" cy="{{ $smallCx }}" r="{{ $smallR }}"
                            :stroke-dasharray="{{ $smallC }}"
                            :stroke-dashoffset="{{ $smallC }} * (1 - progressFraction)" />
                </svg>
                <span class="pomo-bubble__time" x-text="formattedTime">{{ gmdate('i:s', $session->phaseDuration()) }}</span>
                <span class="pomo-bubble__phase-icon">
                    <svg x-show="isWorking" width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5c4 0 7 3 7 7s-3 7-7 7-7-3-7-7 3-7 7-7z"/></svg>
                    <svg x-show="! isWorking" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
                </span>
                </span>
            </button>

        @else
            {{-- ==== Idle: кнопка-«пузырёк» для запуска ==== --}}
            <button type="button"
                    class="pomo-bubble"
                    data-phase="work"
                    wire:click="openLauncher"
                    aria-label="Запустить помодоро">
                <span style="color: var(--accent);">
                    <x-ui.icon name="tomato" :size="22" />
                </span>
            </button>
        @endif
    </div>
    </div>
@endif
