import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/website.css',
                'resources/css/menu-qr.css',
                'resources/css/admin.css',
                'resources/js/website.js',
                'resources/js/menu-qr.js',
                'resources/js/admin.js',
            ],
            refresh: true,
        }),
    ],
});
