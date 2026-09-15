import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/blog-editor.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        // Force IPv4 loopback instead of the "localhost" default, which can
        // resolve to the IPv6 [::1] address on Windows. Some browsers/
        // extensions block requests to bracketed IPv6-literal hosts (seen as
        // net::ERR_BLOCKED_BY_CLIENT), and IPv4 loopback is the more
        // universally compatible choice for local dev anyway.
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
        hmr: {
            host: '127.0.0.1',
        },
    },
    build: {
        minify: 'esbuild',
        cssMinify: true,
        cssCodeSplit: true,
        sourcemap: false,
        chunkSizeWarningLimit: 1400,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        if (id.includes('tinymce')) {
                            return 'vendor-tinymce';
                        }
                        if (id.includes('chart.js')) {
                            return 'vendor-charts';
                        }
                        return 'vendor';
                    }
                },
            },
        },
    },
});
