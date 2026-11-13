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
        // Bind to 0.0.0.0 by default so the dev server is reachable from
        // other devices on the local network (for testing on phones, etc.).
        // Override via VITE_DEV_HOST / VITE_DEV_PORT in .env.
        host: process.env.VITE_DEV_HOST || '0.0.0.0',
        port: parseInt(process.env.VITE_DEV_PORT || '5173', 10),
        strictPort: true,
        hmr: {
            host: process.env.VITE_DEV_HOST || '0.0.0.0',
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
