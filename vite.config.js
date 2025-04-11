import { defineConfig } from 'vite'
import path from 'path'
import tailwindcss from 'tailwindcss'
import autoprefixer from 'autoprefixer'

export default defineConfig({
    root: path.resolve(__dirname),
    publicDir: false,
    css: {
        postcss: {
            plugins: [tailwindcss(), autoprefixer()],
        }
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
