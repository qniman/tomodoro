/**
 * Ранний бутстрап: не ES-module, собирается в IIFE и подключается синхронно
 * в base.blade.php до @livewireScripts.
 * Alpine из Livewire инициализирует x-data до того, как выполнится отложенный
 * Vite-бандл (type=module), поэтому все window.* для x-data регистрируются здесь.
 */
import { createPomoWidgetState } from './pomodoro.js';
import { registerCommandPalette } from './command-palette.js';

if (typeof window !== 'undefined') {
    window.pomoWidget = (initial) => createPomoWidgetState(initial);
    registerCommandPalette();
}
