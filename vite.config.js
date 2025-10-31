import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/sidebar.css',
                'resources/css/products.css',
                'resources/css/layout-optimized.css',
                'resources/css/scheduled-messages.css',
                'resources/js/app.js',
                'resources/js/sidebar.js',
                'resources/js/products.js',
                'resources/js/scheduled-messages.js'
            ],
            refresh: true,
        }),
    ],
});
