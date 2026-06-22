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
