import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        // Alleen nodig als je de dev-server vanaf een ander apparaat opent:
        // zet VITE_HMR_HOST op het LAN-adres van je werkstation.
        hmr: process.env.VITE_HMR_HOST
            ? { host: process.env.VITE_HMR_HOST }
            : undefined,
    },
});
