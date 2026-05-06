import './bootstrap';
import { registerRichEditor } from './editor';
import { registerPomodoroWidget } from './pomodoro';
import { registerCommandPalette } from './command-palette';

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
