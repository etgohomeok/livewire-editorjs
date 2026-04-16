import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
    build: {
        lib: {
            entry: resolve(__dirname, 'src/index.js'),
            formats: ['iife'],
            name: 'LivewireEditorjsBundle',
            fileName: () => 'editor.js',
        },
        outDir: resolve(__dirname, '../dist'),
        emptyOutDir: true,
        minify: true,
    },
});
