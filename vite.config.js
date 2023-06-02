import {fileURLToPath, URL} from "node:url";
import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import vuePlugin from "@vitejs/plugin-vue";

export default defineConfig({
    publicDir: 'public',
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                '@tabler/core/dist/js/tabler.min.js',
                '@tabler/core/dist/css/tabler.min.css',
                '@tabler/core/dist/css/tabler-vendors.min.css',
                '@tabler/core/dist/css/demo.min.css',
                'nprogress/nprogress.css',
                'vue-toastification/dist/index.css'
            ],
        }),
        vuePlugin()
    ],
    resolve: {
        alias: {
            "@": fileURLToPath(new URL("./resources", import.meta.url)),
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    tabler_icons: ["vue-tabler-icons"],
                    toastification: ["vue-toastification"]
                },
            }
        },
    },
});
