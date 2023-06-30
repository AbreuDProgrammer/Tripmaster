import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
              'resources/css/app.css',
              'resources/css/carousel.css',
              'resources/css/rating.css',
              'resources/css/toggle.css',
              'resources/js/app.js',
              'resources/js/carousel.js',
              'resources/js/calendar.js',
              'resources/js/rating.js',
              'resources/js/selectAll.js'
            ],
            refresh: true,
        }),
    ],
});
