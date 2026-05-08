/** Разметка досок задач и редактора задачи: Alpine-компоненты. */

document.addEventListener('alpine:init', () => {
    if (typeof window.Alpine === 'undefined') {
        return;
    }

    Alpine.data('taskBoardSplit', (opts = {}) => ({
        detailMin: typeof opts.minWidth === 'number' ? opts.minWidth : 280,
        detailMax: typeof opts.maxWidth === 'number' ? opts.maxWidth : 640,
        detailW: 420,
        dragging: false,
        startX: 0,
        startW: 420,

        init() {
            try {
                const raw = localStorage.getItem(opts.storageKey || 'tomodoro:taskDetailW');
                const n = raw ? Number.parseInt(raw, 10) : Number.NaN;
                if (!Number.isNaN(n)) {
                    this.detailW = Math.min(Math.max(n, this.detailMin), this.detailMax);
                }
            } catch (_) {
                /* ignore */
            }
        },

        persist() {
            try {
                localStorage.setItem(opts.storageKey || 'tomodoro:taskDetailW', String(this.detailW));
            } catch (_) {
                /* ignore */
            }
        },

        beginResize(ev) {
            ev.preventDefault();
            ev.stopPropagation();
            this.dragging = true;
            this.startX = ev.clientX;
            this.startW = this.detailW;
            document.body.classList.add('is-resizing-split');
            document.documentElement.style.userSelect = 'none';
            document.documentElement.style.cursor = 'col-resize';
        },

        onMove(ev) {
            if (!this.dragging) {
                return;
            }
            /** Ручка между списком и панелью: движение вправо уменьшает ширину панели. */
            const dx = ev.clientX - this.startX;
            const next = Math.round(
                Math.min(this.detailMax, Math.max(this.detailMin, this.startW - dx)),
            );
            this.detailW = next;
        },

        endResize() {
            if (!this.dragging) {
                return;
            }
            this.dragging = false;
            document.body.classList.remove('is-resizing-split');
            document.documentElement.style.userSelect = '';
            document.documentElement.style.cursor = '';
            this.persist();
        },

        taskBoardRailStyle() {
            const open = Boolean(this.$wire?.selectedTaskId);

            if (!open) {
                return {
                    '--task-rail-width': '0px',
                    '--task-board-gap': '0px',
                };
            }

            /* Рельса: разделитель 6px + панель задачи (одна ячейка сетки — плавнее, чем три трека). */
            const railPx = Math.round(Math.min(this.detailMax, Math.max(this.detailMin, this.detailW)) + 6);

            return {
                '--task-rail-width': `${railPx}px`,
                '--task-board-gap': 'var(--s-5)',
            };
        },
    }));

    Alpine.data('taskTagPicker', (tags) => ({
        tags: Array.isArray(tags) ? tags : [],
        q: '',

        get filtered() {
            const trim = this.q.trim().toLowerCase();
            if (!trim) {
                return this.tags;
            }
            return this.tags.filter((t) => String(t?.name ?? '').toLowerCase().includes(trim));
        },
    }));
});
