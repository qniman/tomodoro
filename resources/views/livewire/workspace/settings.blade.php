<div class="ws ws--full">
    <div class="ws__header">
        <div class="ws__head-left">
            <h1 class="ws__title">Настройки</h1>
            <span class="ws__subtitle">Профиль, теги, безопасность, внешний вид и помодоро</span>
        </div>
    </div>

    <div class="ws__body">
        <div class="settings-shell">
            <nav class="settings-nav" aria-label="Разделы настроек">
                @php
                    $tabs = [
                        'profile'    => ['Профиль',     'user'],
                        'tags'       => ['Теги',        'tag'],
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

                        {{-- Имя --}}
                        <form wire:submit.prevent="saveName" class="vstack gap-4" style="margin-bottom: 4px;">
                            <div class="settings-row">
                                <div>
                                    <div class="settings-row__label">Имя</div>
                                    <div class="settings-row__hint">Покажем в шапке и в комментариях.</div>
                                </div>
                                <div>
                                    <x-ui.input wire:model="name" :error="$errors->first('name')" maxlength="120" />
                                </div>
                            </div>
                            <div class="hstack gap-2" style="justify-content: flex-end;">
                                <x-ui.button type="submit" variant="primary" icon="check" wireTarget="saveName">
                                    Сохранить имя
                                </x-ui.button>
                            </div>
                        </form>

                        {{-- Email --}}
                        <div class="settings-row" style="padding-top: 20px; border-top: 1px solid var(--border);">
                            <div>
                                <div class="settings-row__label">Электронная почта</div>
                                <div class="settings-row__hint">Используется для входа. Изменение требует подтверждения.</div>
                            </div>
                            <div>
                                @if($emailChangeSent)
                                    {{-- Ожидает подтверждения --}}
                                    <div style="margin-bottom: 10px;">
                                        <span style="font-size: var(--fz-sm); color: var(--text-muted);">Ожидает подтверждения:</span>
                                        <strong style="font-size: var(--fz-sm); margin-left: 4px;">{{ auth()->user()->pending_email }}</strong>
                                    </div>
                                    <p style="font-size: var(--fz-sm); color: var(--text-muted); margin: 0 0 12px;">
                                        Введите 6-значный код из письма, отправленного на новый адрес.
                                    </p>
                                    <form wire:submit.prevent="confirmEmailChange" class="vstack gap-2">
                                        <x-ui.input
                                            wire:model="emailChangeCode"
                                            placeholder="000000"
                                            maxlength="6"
                                            :error="$errors->first('emailChangeCode')"
                                        />
                                        <div class="hstack gap-2">
                                            <x-ui.button type="submit" variant="primary" icon="check" wireTarget="confirmEmailChange">
                                                Подтвердить
                                            </x-ui.button>
                                            <x-ui.button type="button" variant="ghost" wire:click="cancelEmailChange">
                                                Отмена
                                            </x-ui.button>
                                        </div>
                                    </form>
                                @else
                                    {{-- Текущий email + форма смены --}}
                                    <div style="margin-bottom: 10px;">
                                        <span style="font-size: var(--fz-sm); font-weight: 500;">{{ $email }}</span>
                                        @if(auth()->user()->email_verified_at)
                                            <span style="display: inline-flex; align-items: center; gap: 3px; margin-left: 8px; font-size: 11px; font-weight: 600; color: var(--success); text-transform: uppercase; letter-spacing: 0.5px;">
                                                <x-ui.icon name="check" :size="11" /> Подтверждён
                                            </span>
                                        @else
                                            <span style="display: inline-flex; align-items: center; gap: 3px; margin-left: 8px; font-size: 11px; font-weight: 600; color: var(--warning); text-transform: uppercase; letter-spacing: 0.5px;">
                                                ⚠ Не подтверждён
                                            </span>
                                        @endif
                                    </div>
                                    <form wire:submit.prevent="requestEmailChange" class="vstack gap-2">
                                        <x-ui.input
                                            type="email"
                                            wire:model="newEmail"
                                            placeholder="Новый email"
                                            :error="$errors->first('newEmail')"
                                        />
                                        <div class="hstack gap-2" style="justify-content: flex-end;">
                                            <x-ui.button type="submit" variant="ghost" icon="mail" wireTarget="requestEmailChange">
                                                Сменить email
                                            </x-ui.button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </div>
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

                {{-- ===== Теги ===== --}}
                @if($tab === 'tags')
                    <div class="settings-section">
                        <div class="settings-section__head">
                            <span style="color: var(--accent);"><x-ui.icon name="tag" :size="20" /></span>
                            <div class="flex-1">
                                <div class="settings-section__title">Справочник тегов</div>
                                <div class="settings-section__hint">Метки для фильтров и задач: имя, цвет и значок. После сохранения обновится список в других местах приложения.</div>
                            </div>
                        </div>

                        <livewire:workspace.manage-tags-modal :embedded="true" wire:key="settings-tags-crud" />
                    </div>
                @endif

                {{-- ===== Безопасность ===== --}}
                @if($tab === 'security')
                    @if($user->password_is_placeholder)
                        <div class="settings-section">
                            <div class="settings-section__head">
                                <span style="color: var(--accent);"><x-ui.icon name="key" :size="20" /></span>
                                <div class="flex-1">
                                    <div class="settings-section__title">Пароль для входа по почте</div>
                                    <div class="settings-section__hint">
                                        Вы вошли через соцсеть или внешний сервис. Задайте свой пароль — тогда на странице входа можно авторизоваться по email и паролю, не только через VK.
                                    </div>
                                </div>
                            </div>

                            <form wire:submit.prevent="setExternalLoginPassword" class="vstack gap-4" style="max-width: 420px;">
                                <x-ui.input type="password" label="Новый пароль" wire:model="newPassword" :error="$errors->first('newPassword')" autocomplete="new-password" />
                                <x-ui.input type="password" label="Повторите пароль" wire:model="newPasswordConfirmation" autocomplete="new-password" />

                                <div class="hstack gap-2" style="justify-content: flex-end;">
                                    <x-ui.button type="submit" variant="primary" icon="check" wireTarget="setExternalLoginPassword">
                                        Сохранить пароль
                                    </x-ui.button>
                                </div>
                            </form>
                        </div>
                    @endif

                    <div class="settings-section">
                        <div class="settings-section__head">
                            <span style="color: var(--accent);"><x-ui.icon name="shield" :size="20" /></span>
                            <div class="flex-1">
                                <div class="settings-section__title">Сменить пароль</div>
                                <div class="settings-section__hint">
                                    @if($user->password_is_placeholder)
                                        После того как зададите пароль в блоке выше, здесь можно будет менять пароль, указав текущий.
                                    @else
                                        Не короче 8 символов. После смены продолжишь работать без выхода.
                                    @endif
                                </div>
                            </div>
                        </div>

                        <form wire:submit.prevent="changePassword" class="vstack gap-4" style="max-width: 420px;">
                            <x-ui.input
                                type="password"
                                label="Текущий пароль"
                                wire:model="currentPassword"
                                :error="$errors->first('currentPassword')"
                                autocomplete="current-password"
                                :disabled="$user->password_is_placeholder"
                            />
                            <x-ui.input type="password" label="Новый пароль" wire:model="newPassword" :error="$errors->first('newPassword')" autocomplete="new-password" :disabled="$user->password_is_placeholder" />
                            <x-ui.input type="password" label="Повторите новый пароль" wire:model="newPasswordConfirmation" autocomplete="new-password" :disabled="$user->password_is_placeholder" />

                            <div class="hstack gap-2" style="justify-content: flex-end;">
                                <x-ui.button type="submit" variant="primary" icon="check" wireTarget="changePassword" :disabled="$user->password_is_placeholder">
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

                    <div class="settings-section">
                        <div class="settings-section__head">
                            <span style="color: var(--accent);"><x-ui.icon name="sparkles" :size="20" /></span>
                            <div class="flex-1">
                                <div class="settings-section__title">Нововведения</div>
                                <div class="settings-section__hint">После обновлений приложение может показывать краткое описание изменений. Отключите, если не хотите видеть это окно.</div>
                            </div>
                        </div>

                        <div class="settings-row" style="align-items: center;">
                            <x-ui.switch wire:model.live="hideChangelogModal" class="flex-shrink-0">
                                Не показывать окно «Что нового»
                            </x-ui.switch>
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
