/**
 * Ранний бутстрап для плавающего помидора: не ES-module, собирается в IIFE и
 * подключается синхронно в base.blade.php до @livewireScripts.
 * Иначе Alpine из Livewire успевает инициализировать x-data раньше, чем
 * выполнится отложенный Vite-бандл (type=module).
 */
import { createPomoWidgetState } from './pomodoro.js';

if (typeof window !== 'undefined') {
    window.pomoWidget = (initial) => createPomoWidgetState(initial);
}
