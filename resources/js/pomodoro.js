/**
 * Плавающий помодоро: локальный тикающий интерфейс + редкие sync с Livewire.
 *
 * После morph Livewire значения в x-data не переинициализируются,
 * поэтому на корне .pomo-mount и на .pomo-live дублируются data-pomo-*.
 * При смене паузы/фазы серверный wire:key на .pomo-live пересоздаёт Alpine с актуальным @js().
 * Дополнительно перечитываем data-pomo-* по morph (morphed, morph.updated для .pomo-mount) и navigation.
 */

const pad = (n) => String(n).padStart(2, '0');
const fmt = (seconds) => {
    const s = Math.max(0, Math.floor(seconds));
    return `${pad(Math.floor(s / 60))}:${pad(s % 60)}`;
};

/** Корень data — .pomo-mount; локальный Alpine-компонент — .pomo-live (может быть несколько после morph). */
/** @param {Element | ParentNode | null | undefined} root */
function syncPomoPanelsFromDom(root) {
    const Alpine = typeof window !== 'undefined' ? window.Alpine : undefined;
    if (!root || !(root instanceof Element) || typeof Alpine === 'undefined' || typeof Alpine.$data !== 'function') {
        return;
    }

    const mounts = [];

    if (root.classList?.contains?.('pomo-mount')) {
        mounts.push(root);
    }

    root.querySelectorAll?.('.pomo-mount')?.forEach((el) => mounts.push(el));

    const seen = new Set();

    for (const mount of mounts) {
        if (!(mount instanceof Element) || seen.has(mount)) {
            continue;
        }

        seen.add(mount);

        const lives = mount.querySelectorAll(':scope .pomo-live');

        lives.forEach((live) => {
            try {
                const cmp = Alpine.$data(live);

                if (cmp && typeof cmp.syncFromDataset === 'function') {
                    cmp.syncFromDataset();
                }
            } catch (_) {
                /* нет Alpine */
            }
        });
    }
}

let pomodoroMorphHooksRegistered = false;
let pomodoroLivewireListenAttached = false;

/** Один объект состояния для x-data / window.pomoWidget. */
export function createPomoWidgetState(initial = {}) {
    return {
        phase: initial?.phase ?? 'work',
        phaseDuration: initial?.phase_duration ?? initial?.phaseDuration ?? 1500,
        phaseStartedAtMs: initial?.phase_started_at_ms ?? initial?.phaseStartedAtMs ?? null,
        pausedAtMs: initial?.paused_at_ms ?? initial?.pausedAtMs ?? null,
        completed: initial?.completed ?? 0,
        total: initial?.total ?? 0,

        now: Date.now(),
        tickHandle: null,
        heartbeatHandle: null,
        lastHeartbeatRemaining: null,

        syncFromDataset() {
            const mount = typeof this.$el?.closest === 'function'
                ? this.$el.closest('.pomo-mount')
                : null;
            const liveEl = this.$el;
            /* На .pomo-live дублируем data-pomo-* — morph обновляет сам корень Alpine раньше/надёжнее родителя. */
            const d = {
                ...(mount instanceof Element ? { ...mount.dataset } : {}),
                ...(liveEl?.dataset ? { ...liveEl.dataset } : {}),
            };
            const p = d.pomoPhase;
            if (p !== undefined && p !== null && String(p).length > 0) {
                this.phase = String(p);
            }

            const dur = Number.parseInt(d.pomoDuration ?? '', 10);
            if (!Number.isNaN(dur) && dur > 0) {
                this.phaseDuration = dur;
            }

            const started = Number.parseInt(d.pomoPhaseStartedMs ?? '', 10);
            this.phaseStartedAtMs = Number.isNaN(started) || started <= 0 ? null : started;

            const pausedStr = String(d.pomoPausedMs ?? '');
            if (pausedStr === '') {
                this.pausedAtMs = null;
            } else {
                const paused = Number.parseInt(pausedStr, 10);
                this.pausedAtMs = Number.isNaN(paused) || paused <= 0 ? null : paused;
            }

            const c = Number.parseInt(d.pomoCompleted ?? '', 10);
            if (!Number.isNaN(c) && c >= 0) {
                this.completed = c;
            }

            const t = Number.parseInt(d.pomoTotal ?? '', 10);
            if (!Number.isNaN(t) && t >= 0) {
                this.total = t;
            }

            this.lastHeartbeatRemaining = null;
            this.now = Date.now();
        },

        init() {
            this.syncFromDataset();
            this.startTicking();
            this.scheduleHeartbeat();

            this.$el.addEventListener('alpine:destroyed', () => this.destroy(), { once: true });
        },

        destroy() {
            this.cleanup();
        },

        cleanup() {
            if (this.tickHandle) cancelAnimationFrame(this.tickHandle);
            if (this.heartbeatHandle) clearInterval(this.heartbeatHandle);
            this.tickHandle = null;
            this.heartbeatHandle = null;
        },

        startTicking() {
            const loop = () => {
                this.now = Date.now();
                this.tickHandle = requestAnimationFrame(loop);
            };
            this.tickHandle = requestAnimationFrame(loop);
        },

        scheduleHeartbeat() {
            this.heartbeatHandle = setInterval(() => {
                if (!this.phaseStartedAtMs || this.pausedAtMs) return;
                if (this.phase !== 'work') return;
                if (this.$wire && typeof this.$wire.tick === 'function') {
                    this.$wire.tick();
                }
            }, 20000);
        },

        get remainingSeconds() {
            if (!this.phaseStartedAtMs) return this.phaseDuration;
            const referenceMs = this.pausedAtMs || this.now;
            const elapsed = Math.max(0, Math.floor((referenceMs - this.phaseStartedAtMs) / 1000));
            const remaining = Math.max(0, this.phaseDuration - elapsed);

            if (
                remaining === 0
                && this.phaseStartedAtMs
                && !this.pausedAtMs
                && this.lastHeartbeatRemaining !== 0
            ) {
                this.lastHeartbeatRemaining = 0;
                if (this.$wire && typeof this.$wire.phaseFinished === 'function') {
                    queueMicrotask(() => this.$wire.phaseFinished());
                }
            }

            return remaining;
        },

        get progressFraction() {
            if (this.phaseDuration <= 0) return 0;
            return Math.max(0, Math.min(1, 1 - (this.remainingSeconds / this.phaseDuration)));
        },

        get formattedTime() {
            return fmt(this.remainingSeconds);
        },

        get isPaused() {
            return !!this.pausedAtMs;
        },

        get isWorking() {
            return this.phase === 'work';
        },

        get phaseLabel() {
            if (this.phase === 'short_break') return 'Короткий перерыв';
            if (this.phase === 'long_break') return 'Длинный перерыв';
            return 'Фокус';
        },
    };
}

function registerPomodoroMorphSync() {
    if (typeof document === 'undefined') {
        return;
    }

    /**
     * После morph UI один rAF часто недостаточен: Alpine ещё сливает состояние с DOM.
     * Двойной rAF — до следующего кадра после применения атрибутов.
     */
    const scheduleSync = (root) => {
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                if (root instanceof Element) {
                    syncPomoPanelsFromDom(root);
                } else {
                    syncPomoPanelsFromDom(document.body);
                }
            });
        });
    };

    const syncAllFromBody = () => scheduleSync(null);

    const attach = () => {
        const Livewire = window.Livewire;
        if (! Livewire || typeof Livewire.hook !== 'function') {
            return;
        }

        if (!pomodoroMorphHooksRegistered) {
            pomodoroMorphHooksRegistered = true;
            /*
             * morph.updated по дочерним узлам (.pomo-live) срабатывал раньше, чем патчился
             * родитель .pomo-mount — sync читал старые paused / phase_started.
             * Синхронизируем только когда обновлён сам .pomo-mount (актуальные data-pomo-*).
             */
            Livewire.hook('morph.updated', ({ el }) => {
                if (!el || typeof el.classList?.contains !== 'function') {
                    return;
                }
                if (el.classList.contains('pomo-mount')) {
                    scheduleSync(el);
                }
            });
            Livewire.hook('morphed', syncAllFromBody);
        }
    };

    if (! pomodoroLivewireListenAttached) {
        pomodoroLivewireListenAttached = true;
        document.addEventListener('livewire:init', attach);
        document.addEventListener('livewire:navigated', syncAllFromBody);
    }

    attach();
}

export function registerPomodoroWidget() {
    if (typeof window === 'undefined') {
        return;
    }

    /* Дублирует ранний IIFE pomodoro-boot.js, когда модуль app.js уже загрузился. */
    window.pomoWidget = (initial) => createPomoWidgetState(initial);

    registerPomodoroMorphSync();
}
