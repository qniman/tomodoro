/**
 * Глобальный command-palette (⌘K / Ctrl+K) и хоткеи навигации.
 * Использует data-action атрибуты — никакой жёсткой связи с серверными именами роутов в JS.
 */

const NAV_LINKS = [
    { id: 'today',    title: 'Сегодня',     hint: 'Задачи на сегодня',         icon: '☼', path: '/app/today',    keys: 'g t' },
    { id: 'inbox',    title: 'Входящие',    hint: 'Без даты и проекта',        icon: '✉',  path: '/app/inbox',    keys: 'g i' },
    { id: 'upcoming', title: 'Предстоящие', hint: 'На ближайшие 14 дней',      icon: '⇒',  path: '/app/upcoming', keys: 'g u' },
    { id: 'all',      title: 'Все задачи',  hint: 'Полный список',             icon: '▤',  path: '/app/all',      keys: 'g a' },
    { id: 'calendar', title: 'Календарь',   hint: 'Месяц / неделя / год',      icon: '📅', path: '/app/calendar', keys: 'g c' },
    { id: 'settings', title: 'Настройки',   hint: 'Профиль, тема, помодоро',   icon: '⚙', path: '/app/settings', keys: 'g s' },
];

const ACTIONS = [
    { id: 'new-task', title: 'Новая задача', hint: 'Открыть быстрое создание',  icon: '＋', event: 'open-quick-add', keys: 'n' },
    { id: 'pomo',     title: 'Запустить помодоро', hint: 'Свободный фокус', icon: '🍅', event: 'pomodoro:start', keys: 't' },
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
                // Не реагируем на ввод в текстовые поля.
                const tag = e.target?.tagName;
                const isEditable = tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT'
                    || e.target?.isContentEditable;

                // Используем e.code (KeyK, KeyG, etc.) вместо e.key,
                // чтобы Ctrl+K работал и на кириллической раскладке (где key='л').
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
                    if (window.Livewire) window.Livewire.dispatch('open-quick-add');
                    return;
                }

                // / — поиск (открыть command palette)
                if (e.key === '/' && ! this.open) {
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

                // t — открыть запуск помодоро
                if (codeLetter === 't') {
                    e.preventDefault();
                    if (window.Livewire) window.Livewire.dispatch('pomodoro:start');
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

            get items() {
                const all = [
                    { kind: 'group', label: 'Навигация' },
                    ...NAV_LINKS.map(x => ({ kind: 'nav', ...x })),
                    { kind: 'group', label: 'Действия' },
                    ...ACTIONS.map(x => ({ kind: 'action', ...x })),
                ];
                if (! this.query) return all;
                const q = this.query.toLowerCase().trim();
                return all
                    .filter(it => it.kind === 'group' ||
                        it.title.toLowerCase().includes(q) ||
                        (it.hint || '').toLowerCase().includes(q));
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
                if (! sel) return;
                this.runItem(sel);
            },

            runItem(item) {
                if (item.kind === 'nav' && item.path) this.navigate(item.path);
                if (item.kind === 'action' && item.event && window.Livewire) {
                    window.Livewire.dispatch(item.event);
                }
                this.close();
            },
        };
    };
}
