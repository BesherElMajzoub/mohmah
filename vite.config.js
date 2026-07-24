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
            //
            // `subsets` is NOT optional here. It defaults to ['latin'], which
            // ships @font-face blocks whose unicode-range covers only Latin —
            // so every Arabic glyph on the site, which is all of them, would
            // silently fall back to Segoe UI while the real fonts download and
            // go unused.
            fonts: [
                // Headings — a modern Kufi with the geometry the identity calls
                // for. Never used for body copy.
                //
                // 500 is the heading weight set in app.css; 400 is what the
                // `font-display` utility renders at on non-heading elements.
                // No 700: nothing on the site asks for it.
                bunny('Noto Kufi Arabic', {
                    weights: [400, 500],
                    subsets: ['arabic', 'latin'],
                    // Preloading every variant would put ~5 font requests in
                    // front of the hero image. Only the heading weight is in
                    // the LCP path.
                    preload: [{ weight: 500 }],
                }),
                // Body — chosen for sustained reading at small sizes in Arabic.
                bunny('IBM Plex Sans Arabic', {
                    weights: [400, 500, 600],
                    subsets: ['arabic', 'latin'],
                    preload: [{ weight: 400 }],
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
