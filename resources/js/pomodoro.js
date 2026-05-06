/**
 * Floating pomodoro timer client controller.
 *
 * - Хранит позицию виджета в localStorage (между перезагрузками).
 * - Drag через pointer-события (с учётом краёв окна).
 * - Локальный тикающий таймер на requestAnimationFrame, чтобы интерфейс
 *   не дёргал сервер каждую секунду.
 * - Heartbeat (sync) каждые 20 с — точечно синхронизирует spent_seconds задачи.
 * - При естественном окончании фазы зовёт серверный action phaseFinished.
 */

const STORE_KEY = 'tomodoro:pomo-position';

function pad(n) { return String(n).padStart(2, '0'); }
function fmt(seconds) {
    const s = Math.max(0, Math.floor(seconds));
    return `${pad(Math.floor(s / 60))}:${pad(s % 60)}`;
}

function loadPosition() {
    try {
        const raw = localStorage.getItem(STORE_KEY);
        if (! raw) return null;
        const parsed = JSON.parse(raw);
        if (typeof parsed?.x === 'number' && typeof parsed?.y === 'number') return parsed;
    } catch (e) { /* ignore */ }
    return null;
}
function savePosition(pos) {
    try { localStorage.setItem(STORE_KEY, JSON.stringify(pos)); }
    catch (e) { /* ignore */ }
}

export function registerPomodoroWidget() {
    if (typeof window === 'undefined') return;

    window.pomoWidget = function pomoWidget(initial) {
        return {
            // позиция (px, относительно правого нижнего угла)
            pos: loadPosition() || { x: 24, y: 24 },
            dragging: false,
            dragOffset: null,

            // данные сессии (поддержка обоих стилей ключей из @js)
            phase: initial?.phase ?? 'work',
            phaseDuration: initial?.phase_duration ?? initial?.phaseDuration ?? 1500,
            phaseStartedAtMs: initial?.phase_started_at_ms ?? initial?.phaseStartedAtMs ?? null,
            pausedAtMs: initial?.paused_at_ms ?? initial?.pausedAtMs ?? null,
            completed: initial?.completed ?? 0,
            total: initial?.total ?? 0,

            // тикалка
            now: Date.now(),
            tickHandle: null,
            heartbeatHandle: null,
            lastHeartbeatRemaining: null,

            init() {
                this.applyPosition();
                this.startTicking();
                this.scheduleHeartbeat();

                // Слушатели на window — в шаблоне @pointermove.window ломаются в связке Livewire+Alpine
                // (методы не находятся в области выражения).
                this._winMove = (e) => this.moveDrag(e);
                this._winUp = (e) => this.endDrag(e);
                window.addEventListener('pointermove', this._winMove, { passive: true });
                window.addEventListener('pointerup', this._winUp);
                window.addEventListener('pointercancel', this._winUp);

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

                if (this._winMove) {
                    window.removeEventListener('pointermove', this._winMove);
                    window.removeEventListener('pointerup', this._winUp);
                    window.removeEventListener('pointercancel', this._winUp);
                    this._winMove = null;
                    this._winUp = null;
                }
            },

            // -------- Position / drag --------
            applyPosition() {
                const el = this.$el;
                if (! el) return;
                el.style.right = this.pos.x + 'px';
                el.style.bottom = this.pos.y + 'px';
                el.style.left = 'auto';
                el.style.top = 'auto';
            },

            startDrag(event) {
                if (event.button !== undefined && event.button !== 0) return;
                this.dragging = true;
                const el = this.$el;
                const rect = el.getBoundingClientRect();
                this.dragOffset = {
                    dx: event.clientX - rect.right,
                    dy: event.clientY - rect.bottom,
                };
                el.classList.add('pomo--dragging');
                el.setPointerCapture?.(event.pointerId);
            },
            moveDrag(event) {
                if (! this.dragging || ! this.dragOffset) return;
                const winW = window.innerWidth;
                const winH = window.innerHeight;
                const newRight = winW - event.clientX + this.dragOffset.dx;
                const newBottom = winH - event.clientY + this.dragOffset.dy;
                const el = this.$el;
                const w = el.offsetWidth;
                const h = el.offsetHeight;
                this.pos = {
                    x: Math.max(8, Math.min(newRight, winW - w - 8)),
                    y: Math.max(8, Math.min(newBottom, winH - h - 8)),
                };
                this.applyPosition();
            },
            endDrag(event) {
                if (! this.dragging) return;
                this.dragging = false;
                this.dragOffset = null;
                this.$el.classList.remove('pomo--dragging');
                try {
                    if (event?.pointerId != null && this.$el?.hasPointerCapture?.(event.pointerId)) {
                        this.$el.releasePointerCapture(event.pointerId);
                    }
                } catch (e) { /* ignore */ }
                savePosition(this.pos);
            },

            // -------- Tick --------
            startTicking() {
                const loop = () => {
                    this.now = Date.now();
                    this.tickHandle = requestAnimationFrame(loop);
                };
                this.tickHandle = requestAnimationFrame(loop);
            },

            scheduleHeartbeat() {
                this.heartbeatHandle = setInterval(() => {
                    if (! this.phaseStartedAtMs || this.pausedAtMs) return;
                    if (this.phase !== 'work') return;
                    // вызываем сервер раз в 20 секунд
                    this.$wire.tick();
                }, 20000);
            },

            // -------- Computed --------
            get remainingSeconds() {
                if (! this.phaseStartedAtMs) return this.phaseDuration;
                const referenceMs = this.pausedAtMs || this.now;
                const elapsed = Math.max(0, Math.floor((referenceMs - this.phaseStartedAtMs) / 1000));
                const remaining = Math.max(0, this.phaseDuration - elapsed);

                // Если время вышло — единожды дёргаем сервер.
                if (remaining === 0 && this.phaseStartedAtMs && ! this.pausedAtMs && this.lastHeartbeatRemaining !== 0) {
                    this.lastHeartbeatRemaining = 0;
                    queueMicrotask(() => this.$wire.phaseFinished());
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
                return !! this.pausedAtMs;
            },

            get isRunning() {
                return !! this.phaseStartedAtMs && ! this.pausedAtMs;
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
    };
}
