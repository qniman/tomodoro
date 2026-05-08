import { defineConfig } from 'vite';
import { fileURLToPath } from 'url';
import { dirname, resolve } from 'path';

const __dirname = dirname(fileURLToPath(import.meta.url));

/** IIFE в public/build — синхронная загрузка до @livewireScripts (см. layouts/base.blade.php). */
export default defineConfig({
    build: {
        lib: {
            entry: resolve(__dirname, 'resources/js/pomodoro-boot.js'),
            name: 'pomoBoot',
            formats: ['iife'],
            fileName: () => 'pomodoro-boot.js',
        },
        outDir: 'public/build',
        emptyOutDir: false,
        rollupOptions: {
            output: {
                inlineDynamicImports: true,
            },
        },
    },
});
