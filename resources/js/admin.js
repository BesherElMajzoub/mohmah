/**
 * Admin editor.
 *
 * A TipTap instance configured for Arabic RTL long-form legal writing. The
 * editing surface reuses the public site's .prose-legal styles, so what the
 * client sees while writing is what the article will look like — the single
 * biggest usability difference between this and a bare <textarea>.
 *
 * Registered as an Alpine component so a form can drop in
 * <div x-data="richEditor(...)"> and get a bound editor.
 */

import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Image from '@tiptap/extension-image';
// TipTap v3 ships the table node, row, cell and header as one kit from a
// single package — the separate per-node packages are v2-era.
import { TableKit } from '@tiptap/extension-table';
import TextAlign from '@tiptap/extension-text-align';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('richEditor', (initialContent = '') => ({
        editor: null,

        /** Mirrors the editor state so toolbar buttons can show as active. */
        active: {},

        init() {
            const element = this.$refs.editor;
            const input = this.$refs.input;

            this.editor = new Editor({
                element,
                content: initialContent,
                extensions: [
                    StarterKit.configure({
                        // h1 is the page heading, rendered outside the body.
                        // Allowing it in the editor would produce two h1s on
                        // one page, so the body starts at h2.
                        heading: { levels: [2, 3, 4] },
                    }),
                    Link.configure({
                        openOnClick: false,
                        // Only real web links; javascript: and data: URLs are
                        // rejected rather than stored and rendered later.
                        protocols: ['http', 'https', 'mailto', 'tel'],
                        HTMLAttributes: { rel: 'noopener', target: null },
                    }),
                    Image.configure({ inline: false }),
                    TableKit.configure({ table: { resizable: true } }),
                    TextAlign.configure({ types: ['heading', 'paragraph'] }),
                ],
                editorProps: {
                    attributes: {
                        // dir on the editable surface itself, so typed Arabic
                        // is laid out correctly as it is entered.
                        dir: 'rtl',
                        lang: 'ar',
                        class: 'prose-legal min-h-[24rem] max-w-none px-5 py-4 focus:outline-none',
                    },
                },
                onUpdate: ({ editor }) => {
                    // The form posts a plain input; the editor is a
                    // progressive enhancement over it, not a replacement for
                    // it. If the JS fails, the field still submits its value.
                    input.value = editor.isEmpty ? '' : editor.getHTML();
                    this.syncActive();
                },
                onSelectionUpdate: () => this.syncActive(),
            });

            this.syncActive();
        },

        destroy() {
            this.editor?.destroy();
        },

        syncActive() {
            const e = this.editor;

            this.active = {
                bold: e.isActive('bold'),
                italic: e.isActive('italic'),
                h2: e.isActive('heading', { level: 2 }),
                h3: e.isActive('heading', { level: 3 }),
                bulletList: e.isActive('bulletList'),
                orderedList: e.isActive('orderedList'),
                blockquote: e.isActive('blockquote'),
                link: e.isActive('link'),
            };
        },

        run(command) {
            const chain = this.editor.chain().focus();

            const commands = {
                bold: () => chain.toggleBold().run(),
                italic: () => chain.toggleItalic().run(),
                h2: () => chain.toggleHeading({ level: 2 }).run(),
                h3: () => chain.toggleHeading({ level: 3 }).run(),
                bulletList: () => chain.toggleBulletList().run(),
                orderedList: () => chain.toggleOrderedList().run(),
                blockquote: () => chain.toggleBlockquote().run(),
                hr: () => chain.setHorizontalRule().run(),
                undo: () => chain.undo().run(),
                redo: () => chain.redo().run(),
                table: () => chain.insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run(),
                deleteTable: () => chain.deleteTable().run(),
                addColumn: () => chain.addColumnAfter().run(),
                addRow: () => chain.addRowAfter().run(),
            };

            commands[command]?.();
        },

        setLink() {
            const previous = this.editor.getAttributes('link').href ?? '';
            const url = window.prompt('رابط الوجهة', previous);

            if (url === null) {
                return;
            }

            if (url === '') {
                this.editor.chain().focus().unsetLink().run();

                return;
            }

            this.editor.chain().focus().setLink({ href: url }).run();
        },

        /**
         * Insert an image from the media library.
         *
         * Alt text is required rather than optional: an article image with no
         * Arabic alt is inaccessible and the media library flags it anyway,
         * so the moment of insertion is where it is cheapest to ask.
         */
        insertImage(url, alt) {
            if (!url) {
                return;
            }

            this.editor.chain().focus().setImage({ src: url, alt: alt || '' }).run();
        },
    }));

    /**
     * Repeatable field rows (audience, scope, process steps, FAQs).
     *
     * Rows are re-indexed on removal so the posted array has no gaps, which
     * keeps Laravel's array validation rules straightforward.
     */
    window.Alpine.data('repeater', (initial = [], template = {}) => ({
        rows: initial.length ? initial : [structuredClone(template)],
        template,

        add() {
            this.rows.push(structuredClone(this.template));
        },

        remove(index) {
            this.rows.splice(index, 1);

            if (this.rows.length === 0) {
                this.add();
            }
        },

        move(index, delta) {
            const target = index + delta;

            if (target < 0 || target >= this.rows.length) {
                return;
            }

            [this.rows[index], this.rows[target]] = [this.rows[target], this.rows[index]];
        },
    }));

    /**
     * Slug preview.
     *
     * Shows the client the URL their title will produce before they save,
     * which matters here because the slugs are Arabic and get percent-encoded
     * in the address bar — seeing it up front avoids surprise later.
     */
    window.Alpine.data('slugField', (initial = '', prefix = '') => ({
        slug: initial,
        prefix,

        get preview() {
            return this.slug ? `${this.prefix}/${this.slug}` : `${this.prefix}/…`;
        },
    }));
});
