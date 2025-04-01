import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: [
                'resources/vendor/buildora/js/buildora.js',
                'resources/vendor/buildora/css/buildora.css',
            ],
            refresh: true,
        }),
    ],
})
