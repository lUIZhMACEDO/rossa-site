import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/vendor/htmx-master/dist/htmx.min.js'],
            refresh: true,
        }),
    ],

    server: {
        host: '0.0.0.0',  // Listen on all interfaces inside container
        hmr: {
            host: 'localhost',  // Host the browser connects to for HMR
        },
        // This block is necessary locally to handle font requests to /public/fonts
        proxy: {
            '/fonts': 'http://localhost',
        },
    },
});
