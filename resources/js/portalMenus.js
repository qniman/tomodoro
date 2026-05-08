/**
 * Выпадающие меню с переносом в body (fixed), чтобы не обрезались overflow.
 */

/** Объект из composedPath есть в триггере/меню — не считаем это «вне». */
function eventPathTouchesTriggerOrMenu(event, trigger, menu) {
    const path = typeof event.composedPath === 'function' ? event.composedPath() : [];
    const nodes = [...path];

    if (nodes.length === 0 && event.target instanceof Node) {
        nodes.push(event.target);
        let cur = event.target.parentNode;
        while (cur) {
            nodes.push(cur);
            cur = cur.parentNode;
        }
    }

    for (const node of nodes) {
        if (node === trigger || node === menu) {
            return true;
        }
        if (
            trigger
            && node
            && node.nodeType === 1
            && typeof trigger.contains === 'function'
            && trigger.contains(node)
        ) {
            return true;
        }
        if (
            menu
            && node
            && node.nodeType === 1
            && typeof menu.contains === 'function'
            && menu.contains(node)
        ) {
            return true;
        }
    }

    return false;
}

export function registerPortalMenus() {
    if (typeof document === 'undefined') return;

    document.addEventListener('alpine:init', () => {
        Alpine.data('portalDropdown', (config) => ({
            open: false,
            menuStyle: '',
            align: config.align || 'right',
            directionMode: config.direction || 'auto',
            minWidth: Number(config.minWidth) || 220,

            init() {
                this._mousedown = (e) => {
                    if (! this.open) {
                        return;
                    }
                    if (eventPathTouchesTriggerOrMenu(e, this.$refs.trigger, this.$refs.portalMenu)) {
                        return;
                    }
                    this.open = false;
                };
                document.addEventListener('mousedown', this._mousedown, false);

                this._resize = () => this.open && this.place();
                window.addEventListener('resize', this._resize);

                this.$el.addEventListener(
                    'alpine:destroyed',
                    () => {
                        document.removeEventListener('mousedown', this._mousedown, false);
                        window.removeEventListener('resize', this._resize);
                    },
                    { once: true },
                );
            },

            toggle() {
                this.open = ! this.open;
                if (this.open) {
                    this.$nextTick(() => this.place());
                }
            },

            close() {
                this.open = false;
            },

            place() {
                const tr = this.$refs.trigger?.getBoundingClientRect();
                const menu = this.$refs.portalMenu;
                if (! tr || ! menu) {
                    return;
                }

                const mw = Math.max(menu.offsetWidth || 0, this.minWidth);
                const mh = menu.offsetHeight || 240;
                let left = this.align === 'left' ? tr.left : tr.right - mw;
                left = Math.max(8, Math.min(left, window.innerWidth - mw - 8));

                let top = tr.bottom + 6;
                if (this.directionMode === 'up') {
                    top = tr.top - mh - 6;
                } else if (this.directionMode === 'auto') {
                    const spaceBelow = window.innerHeight - tr.bottom;
                    if (spaceBelow < mh + 24 && tr.top > spaceBelow) {
                        top = tr.top - mh - 6;
                    }
                }

                top = Math.max(8, Math.min(top, window.innerHeight - mh - 8));

                const z = getComputedStyle(document.documentElement).getPropertyValue('--z-portal').trim() || '200';
                this.menuStyle = `position:fixed;z-index:${z};top:${top}px;left:${left}px;min-width:${this.minWidth}px;max-height:min(70vh,520px);overflow-y:auto;padding:var(--s-1);box-sizing:border-box;`;
            },
        }));

        Alpine.data('portalMenuSelect', (config) => ({
            open: false,
            menuStyle: '',
            align: config.align || 'left',
            directionMode: config.direction || 'auto',
            minWidth: Number(config.minWidth) || 200,
            property: config.property,

            init() {
                this._mousedown = (e) => {
                    if (! this.open) {
                        return;
                    }
                    if (eventPathTouchesTriggerOrMenu(e, this.$refs.trigger, this.$refs.portalMenu)) {
                        return;
                    }
                    this.open = false;
                };
                document.addEventListener('mousedown', this._mousedown, false);

                this._resize = () => this.open && this.place();
                window.addEventListener('resize', this._resize);

                this.$el.addEventListener(
                    'alpine:destroyed',
                    () => {
                        document.removeEventListener('mousedown', this._mousedown, false);
                        window.removeEventListener('resize', this._resize);
                    },
                    { once: true },
                );
            },

            toggle() {
                this.open = ! this.open;
                if (this.open) {
                    this.$nextTick(() => this.place());
                }
            },

            close() {
                this.open = false;
            },

            pick(value) {
                if (this.$wire && this.property) {
                    this.$wire.set(this.property, value);
                }
                this.open = false;
            },

            place() {
                const tr = this.$refs.trigger?.getBoundingClientRect();
                const menu = this.$refs.portalMenu;
                if (! tr || ! menu) {
                    return;
                }

                const mw = Math.max(menu.offsetWidth || 0, this.minWidth);
                const mh = menu.offsetHeight || 280;
                let left = this.align === 'right' ? tr.right - mw : tr.left;
                left = Math.max(8, Math.min(left, window.innerWidth - mw - 8));

                let top = tr.bottom + 6;
                if (this.directionMode === 'up') {
                    top = tr.top - mh - 6;
                } else if (this.directionMode === 'auto') {
                    const spaceBelow = window.innerHeight - tr.bottom;
                    if (spaceBelow < mh + 24 && tr.top > spaceBelow) {
                        top = tr.top - mh - 6;
                    }
                }

                top = Math.max(8, Math.min(top, window.innerHeight - mh - 8));

                const z = getComputedStyle(document.documentElement).getPropertyValue('--z-portal').trim() || '200';
                this.menuStyle = `position:fixed;z-index:${z};top:${top}px;left:${left}px;min-width:${this.minWidth}px;max-height:min(70vh,360px);overflow-y:auto;padding:var(--s-1);box-sizing:border-box;`;
            },
        }));
    });
}
