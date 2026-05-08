/**
 * Livewire + Alpine + Tiptap: экземпляр Editor только в WeakMap по узлу [data-content],
 * тулбар вызывает command(editor) после getEditor(content) — без Alpine this.editor и stale closures.
 * Сохранение: saveDescriptionHtmlFromEditor для descriptionHtml (без $wire.set морфинга HTML).
 */

import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Placeholder from '@tiptap/extension-placeholder';
import Link from '@tiptap/extension-link';
import TaskList from '@tiptap/extension-task-list';
import TaskItem from '@tiptap/extension-task-item';

/** @type {WeakMap<Element, Editor>} ключ — контейнер [data-content] */
const EDITORS = new WeakMap();

function getEditor(contentEl) {
    return EDITORS.get(contentEl) ?? null;
}

function setEditor(contentEl, editor) {
    EDITORS.set(contentEl, editor);
}

function destroyEditor(contentEl) {
    const ed = getEditor(contentEl);
    if (! ed) return;
    try { ed.destroy(); } catch (_) { /* ignore */ }
    EDITORS.delete(contentEl);
}

/** Команды: editor подставляется только внутри runCommand (свежий из WeakMap). */
const COMMAND_RUN = {
    bold(ed) {
        ed.chain().focus().toggleBold().run();
    },

    italic(ed) {
        ed.chain().focus().toggleItalic().run();
    },

    strike(ed) {
        ed.chain().focus().toggleStrike().run();
    },

    h1(ed) {
        ed.chain().focus().toggleHeading({ level: 1 }).run();
    },

    h2(ed) {
        ed.chain().focus().toggleHeading({ level: 2 }).run();
    },

    bullet(ed) {
        ed.chain().focus().toggleBulletList().run();
    },

    ord(ed) {
        ed.chain().focus().toggleOrderedList().run();
    },

    todo(ed) {
        ed.chain().focus().toggleTaskList().run();
    },

    quote(ed) {
        ed.chain().focus().toggleBlockquote().run();
    },

    code(ed) {
        ed.chain().focus().toggleCodeBlock().run();
    },

    link(ed) {
        if (ed.isActive('link')) {
            ed.chain().focus().unsetLink().run();
            return;
        }
        const url = window.prompt('URL ссылки');
        if (! url) return;
        ed.chain().focus().setLink({ href: url }).run();
    },

    undo(ed) {
        ed.chain().focus().undo().run();
    },

    redo(ed) {
        ed.chain().focus().redo().run();
    },
};

const ICONS = {
    bold:    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 4h6a4 4 0 0 1 0 8H7zM7 12h7a4 4 0 0 1 0 8H7z"/></svg>',
    italic:  '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 4h-9M14 20H5M15 4 9 20"/></svg>',
    strike:  '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4H9a3 3 0 0 0-2.8 4M14 12a4 4 0 0 1 0 8H6"/><path d="M4 12h16"/></svg>',
    h1:      '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h12M4 6v12M16 6v12"/><path d="M21 6h-3v6h3"/></svg>',
    h2:      '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h12M4 6v12M16 6v12"/><path d="M18 18h4M18 18a2 2 0 1 1 4 0c0 1-1 2-2 3l-2 1"/></svg>',
    list:    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6h13M8 12h13M8 18h13"/><circle cx="4" cy="6" r="1"/><circle cx="4" cy="12" r="1"/><circle cx="4" cy="18" r="1"/></svg>',
    ord:     '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 6h11M10 12h11M10 18h11"/><path d="M4 6h1v4M4 10h2"/><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/></svg>',
    todo:    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="m9 12 2 2 4-4"/></svg>',
    quote:   '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21c3 0 7-1 7-8V5H3v7h4c0 4-1 6-4 6zM14 21c3 0 7-1 7-8V5h-7v7h4c0 4-1 6-4 6z"/></svg>',
    code:    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m16 18 6-6-6-6M8 6l-6 6 6 6"/></svg>',
    link:    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.5.6l3-3a5 5 0 0 0-7.1-7.1l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.5-.6l-3 3a5 5 0 0 0 7.1 7.1l1.7-1.7"/></svg>',
    undo:    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-15-6.7L3 13"/></svg>',
    redo:    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 7v6h-6"/><path d="M3 17a9 9 0 0 1 15-6.7L21 13"/></svg>',
};

/** Порядок тулбара + метаданные (без замыкания на editor в DOM listener). */
const TOOLBAR = [
    {
        id: 'bold',
        icon: 'bold',
        label: 'Жирный',
        active: (ed) => ed.isActive('bold'),
        enabled: (ed) => ed.can().toggleBold(),
    },
    {
        id: 'italic',
        icon: 'italic',
        label: 'Курсив',
        active: (ed) => ed.isActive('italic'),
        enabled: (ed) => ed.can().toggleItalic(),
    },
    {
        id: 'strike',
        icon: 'strike',
        label: 'Зачёркнутый',
        active: (ed) => ed.isActive('strike'),
        enabled: (ed) => ed.can().toggleStrike(),
    },
    { divider: true },
    {
        id: 'h1',
        icon: 'h1',
        label: 'Заголовок 1',
        active: (ed) => ed.isActive('heading', { level: 1 }),
        enabled: (ed) => ed.can().toggleHeading({ level: 1 }),
    },
    {
        id: 'h2',
        icon: 'h2',
        label: 'Заголовок 2',
        active: (ed) => ed.isActive('heading', { level: 2 }),
        enabled: (ed) => ed.can().toggleHeading({ level: 2 }),
    },
    { divider: true },
    {
        id: 'bullet',
        icon: 'list',
        label: 'Список',
        active: (ed) => ed.isActive('bulletList'),
        enabled: (ed) => ed.can().toggleBulletList(),
    },
    {
        id: 'ord',
        icon: 'ord',
        label: 'Нумерация',
        active: (ed) => ed.isActive('orderedList'),
        enabled: (ed) => ed.can().toggleOrderedList(),
    },
    {
        id: 'todo',
        icon: 'todo',
        label: 'Чек-лист',
        active: (ed) => ed.isActive('taskList'),
        enabled: (ed) => ed.can().toggleTaskList(),
    },
    { divider: true },
    {
        id: 'quote',
        icon: 'quote',
        label: 'Цитата',
        active: (ed) => ed.isActive('blockquote'),
        enabled: (ed) => ed.can().toggleBlockquote(),
    },
    {
        id: 'code',
        icon: 'code',
        label: 'Код',
        active: (ed) => ed.isActive('codeBlock'),
        enabled: (ed) => ed.can().toggleCodeBlock(),
    },
    {
        id: 'link',
        icon: 'link',
        label: 'Ссылка',
        active: (ed) => ed.isActive('link'),
        enabled: (ed) =>
            ed.isActive('link') ? ed.can().unsetLink() : ed.can().setLink({ href: 'https://' }),
    },
    { divider: true },
    {
        id: 'undo',
        icon: 'undo',
        label: 'Отменить',
        active: () => false,
        enabled: (ed) => ed.can().undo(),
    },
    {
        id: 'redo',
        icon: 'redo',
        label: 'Повторить',
        active: () => false,
        enabled: (ed) => ed.can().redo(),
    },
];

export function registerRichEditor() {
    if (typeof window === 'undefined') return;

    window.richEditor = function richEditor(wire, prop) {
        return {
            wire,
            prop,
            saveTimer: null,

            init() {
                const shell = this.$el;
                const content = shell.querySelector('[data-content]');
                const toolbar = shell.querySelector('[data-toolbar]');

                if (! content || ! toolbar) {
                    console.warn('[richEditor] missing data-content / data-toolbar', shell);
                    return;
                }

                destroyEditor(content);

                content.innerHTML = '';

                const mount = document.createElement('div');
                content.appendChild(mount);

                const initial = this.readWire() || '';

                const editorInstance = new Editor({
                    element: mount,

                    extensions: [
                        StarterKit.configure({ heading: { levels: [1, 2, 3] } }),
                        Placeholder.configure({ placeholder: 'Опишите задачу — заметки, ссылки, чек-лист…' }),
                        Link.configure({
                            openOnClick: false,
                            autolink: false,
                            HTMLAttributes: { rel: 'noopener noreferrer nofollow' },
                        }),
                        TaskList,
                        TaskItem.configure({ nested: true }),
                    ],

                    content: initial,

                    onUpdate: ({ editor }) => {
                        if (editor.isDestroyed) return;
                        clearTimeout(this.saveTimer);

                        this.saveTimer = setTimeout(() => {
                            const live = getEditor(content);
                            if (! live || live.isDestroyed || live !== editor) return;

                            const html = live.isEmpty ? '' : live.getHTML();
                            this.pushWire(html);
                        }, 500);

                        const live = getEditor(content);
                        if (live && ! live.isDestroyed && live === editor) {
                            this.refreshToolbar(toolbar, live);
                        }
                    },

                    onSelectionUpdate: ({ editor }) => {
                        const live = getEditor(content);
                        if (! live || live.isDestroyed || live !== editor) return;
                        this.refreshToolbar(toolbar, live);
                    },
                });

                setEditor(content, editorInstance);

                this.buildToolbar(toolbar, content);

                const cleanup = () => {
                    clearTimeout(this.saveTimer);
                    this.saveTimer = null;
                    destroyEditor(content);
                };

                shell.addEventListener('alpine:destroyed', cleanup, { once: true });
                window.addEventListener('livewire:navigating', cleanup, { once: true });
            },

            readWire() {
                try {
                    const w = this.wire;
                    if (! w) return '';
                    if (typeof w.get === 'function') {
                        return w.get(this.prop) ?? '';
                    }
                    if (this.prop in w) {
                        return w[this.prop] ?? '';
                    }

                    return '';
                } catch {
                    return '';
                }
            },

            pushWire(html) {
                try {
                    const w = this.wire;
                    if (! w) return;

                    if (this.prop === 'descriptionHtml' && typeof w.call === 'function') {
                        w.call('saveDescriptionHtmlFromEditor', html);
                        return;
                    }
                    if (typeof w.set === 'function') {
                        w.set(this.prop, html);
                        return;
                    }
                    if (typeof w.$set === 'function') {
                        w.$set(this.prop, html);
                        return;
                    }

                    w[this.prop] = html;
                } catch (e) {
                    console.error('[richEditor] push failed', e);
                }
            },

            /**
             * @param {Element} contentEl узел [data-content]
             * @param {string} cmdId ключ COMMAND_RUN
             */
            runCommand(contentEl, cmdId) {
                const runner = COMMAND_RUN[cmdId];
                if (! runner || typeof runner !== 'function') return;

                const ed = getEditor(contentEl);

                if (! ed || ed.isDestroyed) return;

                try {
                    runner(ed);
                    const tb = this.$el?.querySelector('[data-toolbar]');
                    if (tb) this.refreshToolbar(tb, ed);
                } catch (e) {
                    console.error('[richEditor] command failed', e);
                }
            },

            buildToolbar(toolbar, contentEl) {
                toolbar.innerHTML = '';

                TOOLBAR.forEach((item) => {
                    if (item.divider) {
                        const divider = document.createElement('span');
                        divider.className = 'editor__divider';
                        toolbar.appendChild(divider);
                        return;
                    }

                    const id = /** @type {string} */ (item.id);

                    const btn = document.createElement('button');

                    btn.type = 'button';
                    btn.className = 'editor__btn';
                    btn.dataset.command = id;
                    btn.title = item.label;
                    btn.setAttribute('aria-label', item.label);
                    btn.innerHTML = ICONS[item.icon || id] ?? '';

                    btn.addEventListener('mousedown', (e) => {
                        e.preventDefault();
                        const cmdId = btn.dataset.command;
                        if (! cmdId || ! COMMAND_RUN[cmdId]) return;
                        this.runCommand(contentEl, cmdId);
                    });

                    toolbar.appendChild(btn);
                });

                const ed = getEditor(contentEl);

                if (ed && ! ed.isDestroyed) {
                    this.refreshToolbar(toolbar, ed);
                }
            },

            refreshToolbar(toolbar, editor) {
                if (! editor || editor.isDestroyed || ! toolbar) return;

                toolbar.querySelectorAll('button[data-command]').forEach((btn) => {
                    const id = btn.dataset.command;
                    const meta = TOOLBAR.find((t) => t.id === id);
                    if (! meta || meta.divider) return;

                    try {
                        btn.classList.toggle('is-active', !! meta.active(editor));
                        btn.disabled = ! meta.enabled(editor);
                    } catch { /* ignore */ }
                });
            },
        };
    };
}
