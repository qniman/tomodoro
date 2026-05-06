/**
 * Tiptap для Livewire: `editor-host` с x-data вне `wire:ignore`; внутри один `.editor` с ignore.
 * Цепочки `chain().focus().…` для кнопок и синхронный refresh тулбара на каждом транзакшне
 * дают «Applying a mismatched transaction» рядом с Livewire-морфом.
 * Команды на тулбаре: `view.focus()` + следующий кадр + `commands.*` без focus в chain.
 */

import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Placeholder from '@tiptap/extension-placeholder';
import Link from '@tiptap/extension-link';
import TaskList from '@tiptap/extension-task-list';
import TaskItem from '@tiptap/extension-task-item';

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

function destroyOrphan(contentEl, known) {
    const orphan = /** @type {{ editor?: Editor }} */ (contentEl)?.editor;
    if (orphan && orphan !== known && typeof orphan.destroy === 'function') {
        try { orphan.destroy(); } catch (e) { /* ignore */ }
    }
}

const COMMANDS = [
    { id: 'bold',     label: 'Жирный',      icon: 'bold',   run: (ed) => ed.commands.toggleBold(),       isActive: (ed) => ed.isActive('bold') },
    { id: 'italic',   label: 'Курсив',      icon: 'italic', run: (ed) => ed.commands.toggleItalic(),     isActive: (ed) => ed.isActive('italic') },
    { id: 'strike',   label: 'Зачёркнутый', icon: 'strike', run: (ed) => ed.commands.toggleStrike(),     isActive: (ed) => ed.isActive('strike') },
    { divider: true },
    { id: 'h1',       label: 'Заголовок 1', icon: 'h1',     run: (ed) => ed.commands.toggleHeading({ level: 1 }), isActive: (ed) => ed.isActive('heading', { level: 1 }) },
    { id: 'h2',       label: 'Заголовок 2', icon: 'h2',     run: (ed) => ed.commands.toggleHeading({ level: 2 }), isActive: (ed) => ed.isActive('heading', { level: 2 }) },
    { divider: true },
    { id: 'bullet',   label: 'Список',      icon: 'list',   run: (ed) => ed.commands.toggleBulletList(),  isActive: (ed) => ed.isActive('bulletList') },
    { id: 'ord',      label: 'Нумерация',   icon: 'ord',    run: (ed) => ed.commands.toggleOrderedList(), isActive: (ed) => ed.isActive('orderedList') },
    { id: 'todo',     label: 'Чек-лист',    icon: 'todo',   run: (ed) => ed.commands.toggleTaskList(),    isActive: (ed) => ed.isActive('taskList') },
    { divider: true },
    { id: 'quote',    label: 'Цитата',      icon: 'quote',  run: (ed) => ed.commands.toggleBlockquote(), isActive: (ed) => ed.isActive('blockquote') },
    { id: 'code',     label: 'Код',         icon: 'code',   run: (ed) => ed.commands.toggleCodeBlock(),   isActive: (ed) => ed.isActive('codeBlock') },
    { id: 'link',     label: 'Ссылка',      icon: 'link',   action: 'link',                              isActive: (ed) => ed.isActive('link') },
    { divider: true },
    { id: 'undo',     label: 'Отменить',    icon: 'undo',   run: (ed) => ed.commands.undo(),             isActive: () => false },
    { id: 'redo',     label: 'Повторить',   icon: 'redo',   run: (ed) => ed.commands.redo(),            isActive: () => false },
];

export function registerRichEditor() {
    if (typeof window === 'undefined') return;

    window.richEditor = function richEditor(wire, prop) {
        return {
            wire,
            prop,
            editor: null,
            saveTimer: null,
            _toolbarRefreshRaf: null,

            init() {
                const shell = this.$el;
                const content = shell.querySelector('[data-content]');
                const toolbar = shell.querySelector('[data-toolbar]');
                if (! content || ! toolbar) {
                    console.warn('[richEditor] missing data-content / data-toolbar', shell);
                    return;
                }

                destroyOrphan(content, this.editor);
                if (this.editor) {
                    try { this.editor.destroy(); } catch (e) { /* ignore */ }
                    this.editor = null;
                }
                toolbar.innerHTML = '';
                content.textContent = '';

                const initial = this.readWire() || '';

                const scheduleToolbarRefresh = () => {
                    if (this._toolbarRefreshRaf != null) cancelAnimationFrame(this._toolbarRefreshRaf);
                    this._toolbarRefreshRaf = requestAnimationFrame(() => {
                        this._toolbarRefreshRaf = null;
                        if (! this.editor || this.editor.isDestroyed) return;
                        this.refreshToolbar(toolbar);
                    });
                };

                this.editor = new Editor({
                    element: content,
                    extensions: [
                        StarterKit.configure({ heading: { levels: [1, 2, 3] } }),
                        Placeholder.configure({ placeholder: 'Опишите задачу — заметки, ссылки, чек-лист…' }),
                        Link.configure({
                            openOnClick: false,
                            autolink: true,
                            HTMLAttributes: { rel: 'noopener noreferrer nofollow' },
                        }),
                        TaskList,
                        TaskItem.configure({ nested: true }),
                    ],
                    content: initial,
                    onUpdate: ({ editor }) => {
                        if (editor.isDestroyed) return;
                        scheduleToolbarRefresh();
                        const html = editor.isEmpty ? '' : editor.getHTML();
                        clearTimeout(this.saveTimer);
                        this.saveTimer = setTimeout(() => {
                            if (! this.editor || this.editor.isDestroyed) return;
                            this.pushWire(html);
                        }, 600);
                    },
                    onSelectionUpdate: () => scheduleToolbarRefresh(),
                    onTransaction: () => scheduleToolbarRefresh(),
                });

                this.buildToolbar(toolbar);

                const cleanup = () => this.destroy();
                window.addEventListener('livewire:navigating', cleanup, { once: true });
                shell.addEventListener('alpine:destroyed', cleanup, { once: true });
            },

            destroy() {
                clearTimeout(this.saveTimer);
                if (this._toolbarRefreshRaf != null) cancelAnimationFrame(this._toolbarRefreshRaf);
                this._toolbarRefreshRaf = null;
                this.editor?.destroy();
                this.editor = null;
            },

            readWire() {
                try {
                    const w = this.wire;
                    if (! w) return '';
                    if (typeof w.get === 'function') return w.get(this.prop) ?? '';
                    return w[this.prop] ?? '';
                } catch (e) {
                    return '';
                }
            },

            pushWire(html) {
                const w = this.wire;
                if (! w) return;
                try {
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
                    console.error('[richEditor] pushWire failed', e);
                }
            },

            deferEditorCommand(fn) {
                queueMicrotask(() => {
                    requestAnimationFrame(() => {
                        const inst = this.editor;
                        if (! inst || inst.isDestroyed || ! inst.view) return;
                        try {
                            inst.view.focus();
                            requestAnimationFrame(() => {
                                const ed = this.editor;
                                if (! ed || ed.isDestroyed) return;
                                fn(ed);
                            });
                        } catch (e) {
                            console.error('[richEditor] deferEditorCommand failed', e);
                        }
                    });
                });
            },

            buildToolbar(toolbar) {
                toolbar.innerHTML = '';
                COMMANDS.forEach((cmd) => {
                    if (cmd.divider) {
                        const d = document.createElement('span');
                        d.className = 'editor__divider';
                        toolbar.appendChild(d);
                        return;
                    }
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'editor__btn';
                    btn.dataset.command = cmd.id || '';
                    btn.title = cmd.label || '';
                    btn.setAttribute('aria-label', cmd.label || '');
                    btn.innerHTML = ICONS[cmd.icon || 'bold'] || '';
                    btn.addEventListener('mousedown', (e) => e.preventDefault());
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        if (cmd.action === 'link') {
                            this.deferEditorCommand(() => this.applyLinkToggle());
                            return;
                        }
                        const runFn = cmd.run;
                        if (! runFn) return;
                        this.deferEditorCommand((ed) => {
                            try { runFn(ed); }
                            catch (err) { console.error('[richEditor] command failed', cmd.id, err); }
                        });
                    });
                    toolbar.appendChild(btn);
                });
                this.refreshToolbar(toolbar);
            },

            refreshToolbar(toolbar) {
                const ed = this.editor;
                if (! ed || ed.isDestroyed || ! toolbar) return;
                toolbar.querySelectorAll('button[data-command]').forEach((btn) => {
                    const id = btn.dataset.command;
                    const cmd = COMMANDS.find(c => c.id === id);
                    if (! cmd?.isActive) return;
                    try {
                        btn.classList.toggle('is-active', !! cmd.isActive(ed));
                    } catch (e) { /* ignore */ }
                });
            },

            applyLinkToggle() {
                const ed = this.editor;
                if (! ed || ed.isDestroyed) return;
                try {
                    if (ed.isActive('link')) {
                        ed.commands.unsetLink();
                        return;
                    }
                    const url = window.prompt('URL ссылки');
                    if (! url) return;
                    ed.commands.setLink({ href: url });
                } catch (e) {
                    console.error('[richEditor] link toggle failed', e);
                }
            },
        };
    };
}
