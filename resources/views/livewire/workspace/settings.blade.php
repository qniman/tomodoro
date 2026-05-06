<div class="ws ws--full">
    <div class="ws__header">
        <div class="ws__head-left">
            <h1 class="ws__title">Настройки</h1>
            <span class="ws__subtitle">Профиль, безопасность, внешний вид и поведение помодоро</span>
        </div>
    </div>

    <div class="ws__body">
        <div class="settings-shell">
            <nav class="settings-nav" aria-label="Разделы настроек">
                @php
                    $tabs = [
                        'profile'    => ['Профиль',     'user'],
                        'security'   => ['Безопасность', 'shield'],
                        'appearance' => ['Внешний вид', 'palette'],
                        'pomodoro'   => ['Помодоро',    'tomato'],
                        'shortcuts'  => ['Хоткеи',      'command'],
                    ];
                @endphp
                @foreach($tabs as $key => [$label, $icon])
                    <button type="button"
                            class="settings-nav__item {{ $tab === $key ? 'is-active' : '' }}"
                            wire:click="setTab('{{ $key }}')">
                        <x-ui.icon :name="$icon" :size="16" />
                        <span>{{ $label }}</span>
                    </button>
                @endforeach
            </nav>

            <div class="settings-pane">
                {{-- ===== Профиль ===== --}}
                @if($tab === 'profile')
                    <div class="settings-section">
                        <div class="settings-section__head">
                            <span style="color: var(--accent);"><x-ui.icon name="user" :size="20" /></span>
                            <div class="flex-1">
                                <div class="settings-section__title">Личные данные</div>
                                <div class="settings-section__hint">Имя и почта используются в авторизации и в подписях к задачам.</div>
                            </div>
                        </div>

                        <form wire:submit.prevent="saveProfile" class="vstack gap-4">
                            <div class="settings-row">
                                <div>
                                    <div class="settings-row__label">Имя</div>
                                    <div class="settings-row__hint">Покажем в шапке и в комментариях.</div>
                                </div>
                                <div>
                                    <x-ui.input wire:model="name" :error="$errors->first('name')" maxlength="120" />
                                </div>
                            </div>

                            <div class="settings-row">
                                <div>
                                    <div class="settings-row__label">Электронная почта</div>
                                    <div class="settings-row__hint">Используется для входа в систему.</div>
                                </div>
                                <div>
                                    <x-ui.input type="email" wire:model="email" :error="$errors->first('email')" />
                                </div>
                            </div>

                            <div class="hstack gap-2" style="justify-content: flex-end;">
                                <x-ui.button type="submit" variant="primary" icon="check" wireTarget="saveProfile">
                                    Сохранить
                                </x-ui.button>
                            </div>
                        </form>
                    </div>

                    <div class="settings-section">
                        <div class="settings-section__head">
                            <span style="color: var(--accent);"><x-ui.icon name="image" :size="20" /></span>
                            <div class="flex-1">
                                <div class="settings-section__title">Аватар</div>
                                <div class="settings-section__hint">Квадратное изображение, до 2 МБ. Если не задано — покажем инициалы.</div>
                            </div>
                        </div>

                        <div class="settings-avatar">
                            <x-ui.avatar :src="$user->avatar_url" :name="$user->name" size="lg" />

                            <div class="vstack gap-2" style="flex: 1;">
                                <input type="file" wire:model="newAvatar" accept="image/*" class="input" />
                                @error('newAvatar')<span class="field__error">{{ $message }}</span>@enderror
                                <div class="hstack gap-2">
                                    <x-ui.button variant="primary" icon="upload" wire:click="uploadAvatar" wireTarget="uploadAvatar"
                                                 :disabled="empty($newAvatar)">
                                        Загрузить
                                    </x-ui.button>
                                    @if($avatarPath)
                                        <x-ui.button variant="ghost" icon="trash" wire:click="removeAvatar" wire:confirm="Удалить аватар?">
                                            Удалить
                                        </x-ui.button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ===== Безопасность ===== --}}
                @if($tab === 'security')
                    <div class="settings-section">
                        <div class="settings-section__head">
                            <span style="color: var(--accent);"><x-ui.icon name="shield" :size="20" /></span>
                            <div class="flex-1">
                                <div class="settings-section__title">Сменить пароль</div>
                                <div class="settings-section__hint">Не короче 8 символов. После смены продолжишь работать без выхода.</div>
                            </div>
                        </div>

                        <form wire:submit.prevent="changePassword" class="vstack gap-4" style="max-width: 420px;">
                            <x-ui.input type="password" label="Текущий пароль" wire:model="currentPassword" :error="$errors->first('currentPassword')" />
                            <x-ui.input type="password" label="Новый пароль" wire:model="newPassword" :error="$errors->first('newPassword')" />
                            <x-ui.input type="password" label="Повторите новый пароль" wire:model="newPasswordConfirmation" />

                            <div class="hstack gap-2" style="justify-content: flex-end;">
                                <x-ui.button type="submit" variant="primary" icon="check" wireTarget="changePassword">
                                    Сменить пароль
                                </x-ui.button>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- ===== Внешний вид ===== --}}
                @if($tab === 'appearance')
                    <div class="settings-section">
                        <div class="settings-section__head">
                            <span style="color: var(--accent);"><x-ui.icon name="palette" :size="20" /></span>
                            <div class="flex-1">
                                <div class="settings-section__title">Тема</div>
                                <div class="settings-section__hint">Можно следовать системе или зафиксировать оформление.</div>
                            </div>
                        </div>

                        <div class="settings-theme">
                            @foreach([
                                'light' => ['Светлая', 'Тёплый белый фон с акцентом томата.'],
                                'dark'  => ['Тёмная', 'Глубокий графит, мягкий контраст.'],
                                'auto'  => ['Системная', 'Подстраивается под системные настройки.'],
                            ] as $key => [$label, $hint])
                                <button type="button"
                                        data-theme="{{ $key }}"
                                        class="theme-card {{ $theme === $key ? 'is-active' : '' }}"
                                        wire:click="setTheme('{{ $key }}')">
                                    <div class="theme-card__preview"></div>
                                    <div class="theme-card__title">{{ $label }}</div>
                                    <div class="text-xs text-muted" style="margin-top: 2px;">{{ $hint }}</div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ===== Хоткеи ===== --}}
                @if($tab === 'shortcuts')
                    <div class="settings-section">
                        <div class="settings-section__head">
                            <span style="color: var(--accent);"><x-ui.icon name="command" :size="20" /></span>
                            <div class="flex-1">
                                <div class="settings-section__title">Клавиатурные сокращения</div>
                                <div class="settings-section__hint">Главный лайфхак: <kbd class="kbd">Ctrl</kbd> + <kbd class="kbd">K</kbd> открывает командную палитру с поиском по всему приложению.</div>
                            </div>
                        </div>

                        @php
                            $shortcuts = [
                                ['Глобально', [
                                    ['Открыть командную палитру', ['Ctrl', 'K']],
                                    ['Открыть поиск',              ['/']],
                                    ['Запустить помодоро',         ['T']],
                                    ['Создать задачу',             ['N']],
                                ]],
                                ['Навигация (g + клавиша)', [
                                    ['Сегодня',     ['G', 'T']],
                                    ['Входящие',    ['G', 'I']],
                                    ['Предстоящие', ['G', 'U']],
                                    ['Все задачи',  ['G', 'A']],
                                    ['Календарь',   ['G', 'C']],
                                    ['Настройки',   ['G', 'S']],
                                ]],
                                ['Командная палитра', [
                                    ['Двигаться по списку', ['↑', '↓']],
                                    ['Подтвердить выбор',   ['Enter']],
                                    ['Закрыть',             ['Esc']],
                                ]],
                            ];
                        @endphp

                        @foreach($shortcuts as [$group, $items])
                            <div style="margin-top: var(--s-4);">
                                <div class="text-xs text-subtle font-semibold" style="text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: var(--s-2);">
                                    {{ $group }}
                                </div>
                                <div class="vstack gap-2">
                                    @foreach($items as [$label, $keys])
                                        <div class="hstack gap-3" style="justify-content: space-between; padding: var(--s-2) 0; border-bottom: 1px dashed var(--border);">
                                            <span class="text-sm">{{ $label }}</span>
                                            <span class="hstack gap-1">
                                                @foreach($keys as $k)
                                                    <kbd class="kbd">{{ $k }}</kbd>
                                                @endforeach
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- ===== Помодоро ===== --}}
                @if($tab === 'pomodoro')
                    <div class="settings-section">
                        <div class="settings-section__head">
                            <span style="color: var(--accent);"><x-ui.icon name="tomato" :size="20" /></span>
                            <div class="flex-1">
                                <div class="settings-section__title">Помодоро по умолчанию</div>
                                <div class="settings-section__hint">Используются при запуске «свободного фокуса» и для расчёта количества помидорок к задачам.</div>
                            </div>
                        </div>

                        <form wire:submit.prevent="savePomodoro" class="vstack gap-4">
                            <div class="settings-row">
                                <div>
                                    <div class="settings-row__label">Длительность фокуса</div>
                                    <div class="settings-row__hint">5 – 120 минут.</div>
                                </div>
                                <div style="max-width: 140px;">
                                    <x-ui.input type="number" min="5" max="120" wire:model="workMinutes" :error="$errors->first('workMinutes')" />
                                </div>
                            </div>

                            <div class="settings-row">
                                <div>
                                    <div class="settings-row__label">Короткий перерыв</div>
                                    <div class="settings-row__hint">Между обычными помодоро.</div>
                                </div>
                                <div style="max-width: 140px;">
                                    <x-ui.input type="number" min="1" max="60" wire:model="shortBreakMinutes" :error="$errors->first('shortBreakMinutes')" />
                                </div>
                            </div>

                            <div class="settings-row">
                                <div>
                                    <div class="settings-row__label">Длинный перерыв</div>
                                    <div class="settings-row__hint">Каждые N помодоро.</div>
                                </div>
                                <div style="max-width: 140px;">
                                    <x-ui.input type="number" min="1" max="90" wire:model="longBreakMinutes" :error="$errors->first('longBreakMinutes')" />
                                </div>
                            </div>

                            <div class="settings-row">
                                <div>
                                    <div class="settings-row__label">Длинный перерыв каждые</div>
                                    <div class="settings-row__hint">Сколько помодоро между длинными перерывами.</div>
                                </div>
                                <div style="max-width: 140px;">
                                    <x-ui.input type="number" min="2" max="12" wire:model="longBreakEvery" :error="$errors->first('longBreakEvery')" />
                                </div>
                            </div>

                            <div class="hstack gap-2" style="justify-content: flex-end;">
                                <x-ui.button variant="ghost" icon="rotate-ccw" wire:click="resetPomodoro" wire:confirm="Сбросить настройки к стандартным?">
                                    Сбросить к умолчанию
                                </x-ui.button>
                                <x-ui.button type="submit" variant="primary" icon="check" wireTarget="savePomodoro">
                                    Сохранить
                                </x-ui.button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
