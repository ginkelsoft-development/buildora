import { defineConfig } from 'vite'
import path from 'path'
import fs from 'node:fs'
import tailwindcss from '@tailwindcss/vite'
import autoprefixer from 'autoprefixer'

const overrideTheme = path.resolve(__dirname, '../../resources/buildora/buildora-theme.css')
const fallbackTheme = path.resolve(__dirname, 'resources/css/buildora-theme.css')

export default defineConfig({
    root: path.resolve(__dirname),
    publicDir: false,
    plugins: [
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@buildora-theme': fs.existsSync(overrideTheme) ? overrideTheme : fallbackTheme,
        },
    },
    build: {
        outDir: 'dist',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                app: path.resolve(__dirname, 'resources/js/app.js'),
                style: path.resolve(__dirname, 'resources/css/entry.css'),
            },
            output: {
                entryFileNames: 'assets/[name].js',
                assetFileNames: 'assets/[name].[ext]',
            },
        },
    },
})
