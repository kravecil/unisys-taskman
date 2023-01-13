import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { quasar/* , transformAssetUrls */ } from '@quasar/vite-plugin'

export default defineConfig({
    plugins: [
        laravel([
            'resources/js/app.js',
        ]),

        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),

        quasar({
            sassVariables: 'resources/css/quasar-variables.sass'
        })
    ],

    server: {
        host: '127.0.0.1',
        // port: 8089,
        watch: {
            usePolling: true,
        },
    }
});
