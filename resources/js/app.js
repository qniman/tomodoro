import './bootstrap';
import './taskUi';
import Sortable from 'sortablejs';
window.Sortable = Sortable;
import { registerPortalMenus } from './portalMenus';
import { registerRichEditor } from './editor';
import { registerPomodoroWidget } from './pomodoro';
import { registerCommandPalette } from './command-palette';

// Телепорт выпадающих меню до прикрепления к body
registerPortalMenus();

// Tiptap-обёртка для редактора задач
registerRichEditor();

// Плавающий помодоро-таймер
registerPomodoroWidget();

// Командная палитра ⌘K и хоткеи навигации
registerCommandPalette();

// Лёгкие глобальные хелперы темы
function applyTheme(theme) {
    if (! theme) return;
    document.documentElement.dataset.theme = theme;
    try { localStorage.setItem('tomodoro:theme', theme); } catch (e) { /* ignore */ }
    if (window.Alpine?.store('theme')) {
        window.Alpine.store('theme').current = theme;
    }
}

document.addEventListener('alpine:init', () => {
    if (! window.Alpine) return;

    let compactSidebar = false;
    try {
        const saved = localStorage.getItem('tomodoro:sidebarCompact') === '1';
        const narrow = window.matchMedia('(max-width: 920px)').matches;
        compactSidebar = saved && !narrow;
    } catch (e) { /* ignore */
    }

    window.Alpine.store('layout', {
        compactSidebar,
        mobileSidebarOpen: false,
        toggleSidebarCompact() {
            if (typeof window !== 'undefined' && window.matchMedia('(max-width: 920px)').matches) {
                return;
            }
            this.compactSidebar = !this.compactSidebar;
            try {
                localStorage.setItem('tomodoro:sidebarCompact', this.compactSidebar ? '1' : '0');
            } catch (e) { /* ignore */
            }
        },
        openMobileSidebar() {
            this.mobileSidebarOpen = true;
        },
        closeMobileSidebar() {
            this.mobileSidebarOpen = false;
        },
        toggleMobileSidebar() {
            this.mobileSidebarOpen = !this.mobileSidebarOpen;
        },
    });

    const mqMobileLayout = window.matchMedia('(max-width: 920px)');
    const clearCompactOnMobile = () => {
        const layout = window.Alpine.store('layout');
        if (mqMobileLayout.matches && layout.compactSidebar) {
            layout.compactSidebar = false;
            try {
                localStorage.setItem('tomodoro:sidebarCompact', '0');
            } catch (e) { /* ignore */
            }
        }
    };
    mqMobileLayout.addEventListener('change', clearCompactOnMobile);
    queueMicrotask(clearCompactOnMobile);

    window.Alpine.store('theme', {
        current: localStorage.getItem('tomodoro:theme') || 'auto',
        set(value) { applyTheme(value); },
    });

    // На случай, если что-то поставило data-theme до Alpine — синхронизируем стор.
    const initial = document.documentElement.dataset.theme || window.Alpine.store('theme').current;
    window.Alpine.store('theme').current = initial;
});

// Слушаем событие из Livewire-настроек темы.
function pickTheme(detail) {
    if (! detail) return null;
    if (typeof detail === 'string') return detail;
    if (detail.theme) return detail.theme;
    if (Array.isArray(detail) && detail[0]?.theme) return detail[0].theme;
    return null;
}
window.addEventListener('apply-theme', (event) => applyTheme(pickTheme(event.detail)));
window.addEventListener('theme-changed', (event) => applyTheme(pickTheme(event.detail)));
