import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'node:path'

export default defineConfig({
    plugins: [vue()],
    build: {
        outDir: 'public/build',
        manifest: true,
        emptyOutDir: true,
        rollupOptions: {
            input: path.resolve(__dirname, 'resources/js/app.ts')
        }
    }
})
