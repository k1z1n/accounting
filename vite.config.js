import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/applications.js',
                'resources/js/crud.js',
                'resources/js/ag-grid-config.js',
                'resources/js/applications-grid.js',
                'resources/js/transfers-grid.js',
                'resources/js/universal-grid.js',
                'resources/js/mobile-optimization.js',
                'resources/js/admin-grids.js',
                'resources/js/profile-grid.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
