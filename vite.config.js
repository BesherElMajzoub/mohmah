import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            // admin.js is a separate entry so the editor bundle (TipTap and
            // ProseMirror) is never shipped to public visitors.
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin.js'],
            refresh: true,
            // Fonts are downloaded at build time and served from our own
            // origin. Arabic font files are large, so a third-party request in
            // the critical path would cost both a connection and control over
            // caching. Weights are kept deliberately few for the same reason.
            fonts: [
                // Headings — a modern Kufi with the geometry the identity calls
                // for. Never used for body copy.
                bunny('Noto Kufi Arabic', {
                    weights: [400, 500, 700],
                }),
                // Body — chosen for sustained reading at small sizes in Arabic.
                bunny('IBM Plex Sans Arabic', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
