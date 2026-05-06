{{-- Глобальная очередь тостов. Слушает Livewire-события "toast".
     Поддерживаемый payload: { type, title, message, duration, actionLabel, actionEvent, actionPayload } --}}

<div
    class="toast-region"
    x-data="{
        items: [],
        push(item) {
            const data = Array.isArray(item) ? (item[0] || {}) : item;
            const id = data.id || (crypto.randomUUID ? crypto.randomUUID() : ('t-' + Math.random()));
            const ttl = data.duration ?? data.ttl ?? 5500;
            const entry = {
                id,
                type: data.type ?? 'info',
                title: data.title ?? '',
                message: data.message ?? '',
                actionLabel: data.actionLabel ?? null,
                actionEvent: data.actionEvent ?? null,
                actionPayload: data.actionPayload ?? null,
            };
            this.items = [...this.items, entry];
            if (ttl > 0) setTimeout(() => this.dismiss(id), ttl);
        },
        dismiss(id) { this.items = this.items.filter(i => i.id !== id); },
        runAction(item) {
            if (item.actionEvent && window.Livewire) {
                const args = item.actionPayload === null || item.actionPayload === undefined
                    ? []
                    : (Array.isArray(item.actionPayload) ? item.actionPayload : [item.actionPayload]);
                window.Livewire.dispatch(item.actionEvent, args);
            }
            this.dismiss(item.id);
        },
    }"
    x-on:toast.window="push($event.detail)"
>
    <template x-for="t in items" :key="t.id">
        <div class="toast" :class="'toast--' + t.type">
            <span class="toast__icon" x-html="{
                success: '<svg width=\'20\' height=\'20\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M20 6 9 17l-5-5\'/></svg>',
                danger:  '<svg width=\'20\' height=\'20\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><circle cx=\'12\' cy=\'12\' r=\'9\'/><path d=\'M12 8v5M12 16h.01\'/></svg>',
                warning: '<svg width=\'20\' height=\'20\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z\'/><path d=\'M12 9v4M12 17h.01\'/></svg>',
                info:    '<svg width=\'20\' height=\'20\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><circle cx=\'12\' cy=\'12\' r=\'9\'/><path d=\'M12 8h.01M11 12h1v4h1\'/></svg>'
            }[t.type] || ''"></span>

            <div class="toast__body">
                <div class="toast__title" x-text="t.title"></div>
                <div class="toast__message" x-text="t.message" x-show="t.message"></div>
            </div>

            <template x-if="t.actionLabel && t.actionEvent">
                <button
                    type="button"
                    class="toast__action"
                    x-text="t.actionLabel"
                    @click="runAction(t)"
                ></button>
            </template>

            <button type="button" class="toast__close" @click="dismiss(t.id)" aria-label="Закрыть">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6 6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </template>
</div>
