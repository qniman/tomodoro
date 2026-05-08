/**
 * Глобальный command-palette (⌘K / Ctrl+K) и хоткеи навигации.
 * Использует data-action атрибуты — никакой жёсткой связи с серверными именами роутов в JS.
 */

const NAV_LINKS = [
    { id: 'today',                title: 'Сегодня',                 hint: 'Задачи на сегодня',              icon: '☼',  path: '/app/today',                  keys: 'g t' },
    { id: 'inbox',                title: 'Входящие',                hint: 'Задачи без даты и проекта',      icon: '✉',  path: '/app/inbox',                  keys: 'g i' },
    { id: 'upcoming',             title: 'Предстоящие',             hint: 'На ближайшие 14 дней',           icon: '⇒',  path: '/app/upcoming',               keys: 'g u' },
    { id: 'all',                  title: 'Все задачи',              hint: 'Полный список',                  icon: '▤',  path: '/app/all',                    keys: 'g a' },
    { id: 'calendar',             title: 'Календарь',               hint: 'Месяц / неделя / год',           icon: '📅', path: '/app/calendar',               keys: 'g c' },
    { id: 'settings',             title: 'Настройки',               hint: 'Профиль, тема, помодоро',        icon: '⚙',  path: '/app/settings',               keys: 'g s' },
    { id: 'settings-profile',     title: 'Настройки: Профиль',      hint: 'Имя, email, аватар',             icon: '👤', path: '/app/settings?tab=profile',   keys: '' },
    { id: 'settings-appearance',  title: 'Настройки: Внешний вид',  hint: 'Тема, цветовая схема',           icon: '🎨', path: '/app/settings?tab=appearance', keys: '' },
    { id: 'settings-pomodoro',    title: 'Настройки: Помодоро',     hint: 'Длительность сессий, перерывы',  icon: '⏱', path: '/app/settings?tab=pomodoro',  keys: '' },
    { id: 'settings-shortcuts',   title: 'Настройки: Хоткеи',       hint: 'Клавиатурные сокращения',        icon: '⌨', path: '/app/settings?tab=shortcuts', keys: '' },
];

const ACTIONS = [
    { id: 'new-task',        title: 'Новая задача',        hint: 'Создать задачу (с любой страницы)',   icon: '＋', keys: 'n' },
    { id: 'new-event',       title: 'Новое событие',       hint: 'Добавить в календарь',               icon: '📆', keys: '' },
    { id: 'pomo',            title: 'Запустить помодоро',  hint: 'Открыть таймер фокуса',              icon: '🍅', keys: 't' },
    { id: 'theme-cycle',     title: 'Переключить тему',    hint: 'Светлая → Тёмная → Авто',            icon: '🌓', keys: '' },
    { id: 'sidebar-compact', title: 'Компактная панель',   hint: 'Свернуть / развернуть сайдбар',      icon: '⇤',  keys: '' },
];

export function registerCommandPalette() {
    if (typeof window === 'undefined') return;

    window.cmdPalette = function cmdPalette() {
        return {
            open: false,
            query: '',
            highlight: 0,
            sequence: '',
            sequenceTimer: null,

            init() {
                window.addEventListener('keydown', (e) => this.handleGlobalKey(e));
            },

            // -------- Глобальные хоткеи --------
            handleGlobalKey(e) {
                const tag = e.target?.tagName;
                const isEditable = tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT'
                    || e.target?.isContentEditable;

                // e.code (KeyK, KeyG, etc.) вместо e.key — работает на кириллической раскладке.
                const code = e.code || '';
                const codeLetter = code.startsWith('Key') ? code.slice(3).toLowerCase() : '';

                // Открыть палитру: Ctrl/Cmd + K
                if ((e.ctrlKey || e.metaKey) && (codeLetter === 'k' || e.key?.toLowerCase() === 'k')) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.toggle();
                    return;
                }

                // Esc — закрыть палитру.
                if (e.key === 'Escape' && this.open) {
                    this.close();
                    return;
                }

                if (isEditable) return;
                if (e.ctrlKey || e.metaKey || e.altKey) return;

                // n — новая задача
                if (codeLetter === 'n') {
                    e.preventDefault();
                    this.doAction('new-task');
                    return;
                }

                // / — поиск
                if (e.key === '/' && !this.open) {
                    e.preventDefault();
                    this.openWith('');
                    return;
                }

                // Двухклавишные «g X» — навигация.
                if (codeLetter === 'g') {
                    this.startSequence('g');
                    return;
                }

                if (this.sequence === 'g' && codeLetter) {
                    const map = {
                        t: '/app/today',
                        i: '/app/inbox',
                        u: '/app/upcoming',
                        a: '/app/all',
                        c: '/app/calendar',
                        s: '/app/settings',
                    };
                    if (map[codeLetter]) {
                        e.preventDefault();
                        this.navigate(map[codeLetter]);
                    }
                    this.clearSequence();
                    return;
                }

                // t — помодоро
                if (codeLetter === 't') {
                    e.preventDefault();
                    this.doAction('pomo');
                }
            },

            startSequence(prefix) {
                this.sequence = prefix;
                clearTimeout(this.sequenceTimer);
                this.sequenceTimer = setTimeout(() => this.clearSequence(), 1200);
            },
            clearSequence() {
                this.sequence = '';
                clearTimeout(this.sequenceTimer);
            },

            navigate(path) {
                if (window.Livewire?.navigate) {
                    window.Livewire.navigate(path);
                } else {
                    window.location.href = path;
                }
            },

            // -------- Палитра --------
            toggle() { this.open ? this.close() : this.openWith(''); },

            openWith(query) {
                this.query = query;
                this.highlight = 0;
                this.open = true;
                this.$nextTick(() => this.$refs.input?.focus());
            },

            close() {
                this.open = false;
                this.query = '';
                this.highlight = 0;
            },

            // -------- Действия (работают с любой страницы) --------
            doAction(id) {
                const path = window.location.pathname;

                const isTaskPage = /^\/app(\/today|\/inbox|\/upcoming|\/all)?\/?$/.test(path);
                const isCalendarPage = path === '/app/calendar';

                // Паттерн «navigated + dispatch»: переходим, ждём завершения, диспатчим.
                const navThenDispatch = (targetPath, event) => {
                    const onNav = () => {
                        document.removeEventListener('livewire:navigated', onNav);
                        if (window.Livewire) window.Livewire.dispatch(event);
                    };
                    document.addEventListener('livewire:navigated', onNav);
                    this.navigate(targetPath);
                };

                switch (id) {
                    case 'new-task':
                        if (isTaskPage) {
                            if (window.Livewire) window.Livewire.dispatch('open-quick-add');
                        } else {
                            navThenDispatch('/app/today', 'open-quick-add');
                        }
                        break;

                    case 'new-event':
                        if (isCalendarPage) {
                            if (window.Livewire) window.Livewire.dispatch('calendar:open-create-event');
                        } else {
                            navThenDispatch('/app/calendar', 'calendar:open-create-event');
                        }
                        break;

                    case 'pomo':
                        if (window.Livewire) window.Livewire.dispatch('pomodoro:start');
                        break;

                    case 'theme-cycle': {
                        const store = window.Alpine?.store('theme');
                        if (store) {
                            const next = { auto: 'light', light: 'dark', dark: 'auto' };
                            store.set(next[store.current] ?? 'auto');
                        }
                        break;
                    }

                    case 'sidebar-compact':
                        window.Alpine?.store('layout')?.toggleSidebarCompact();
                        break;
                }
            },

            get items() {
                const all = [
                    { kind: 'group', label: 'Навигация' },
                    ...NAV_LINKS.map(x => ({ kind: 'nav', ...x })),
                    { kind: 'group', label: 'Действия' },
                    ...ACTIONS.map(x => ({ kind: 'action', ...x })),
                ];
                if (!this.query) return all;
                const q = this.query.toLowerCase().trim();
                return all.filter(it =>
                    it.kind === 'group' ||
                    it.title.toLowerCase().includes(q) ||
                    (it.hint || '').toLowerCase().includes(q)
                );
            },

            get selectableItems() {
                return this.items.filter(it => it.kind !== 'group');
            },

            isActive(item) {
                const sel = this.selectableItems[this.highlight];
                return sel && sel.id === item.id;
            },

            move(delta) {
                const max = this.selectableItems.length;
                if (max === 0) return;
                this.highlight = (this.highlight + delta + max) % max;
            },

            commit() {
                const sel = this.selectableItems[this.highlight];
                if (!sel) return;
                this.runItem(sel);
            },

            runItem(item) {
                if (item.kind === 'nav' && item.path) {
                    this.navigate(item.path);
                } else if (item.kind === 'action') {
                    this.doAction(item.id);
                }
                this.close();
            },
        };
    };
}
