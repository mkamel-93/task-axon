import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";
import manifestSRI from "vite-plugin-manifest-sri";
import vue from '@vitejs/plugin-vue';

export default defineConfig(({ mode }) => {
    // load all env vars including VITE_*
    const env = loadEnv(mode, process.cwd(), '');

    const appEnv = env.VITE_APP_ENV || (typeof import.meta !== 'undefined' && import.meta.env?.MODE) || mode || 'production';

    const isProduction = appEnv === 'production';

    return {
        optimizeDeps: {
            include: [
                'axios',
            ],
        },
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
                // buildDirectory: 'bundle',
                // hotFile: 'storage/vite.hot',
            }),
            ...(isProduction ? [manifestSRI()] : []),
            tailwindcss(),
            vue(),
        ],
        server: {
            host: '0.0.0.0',
            port: 5173,
            hmr: {
                host: 'localhost',
            },
            cors: true,
            watch: {
                ignored: [
                    '**/storage/framework/views/**',
                    '**/vendor/**',
                    '**/node_modules/**',
                ],
            },
        },
        build: {
            rollupOptions: {
                output: {
                    assetFileNames: (assetInfo) => {
                        const name = assetInfo.name || '';

                        // Font files
                        if (/\.(woff2?|ttf|eot|otf)$/i.test(name)) {
                            return 'fonts/[name]-[hash][extname]';
                        }

                        // Images (including more formats)
                        if (/\.(jpe?g|png|gif|svg|webp|avif|ico)$/i.test(name)) {
                            return 'images/[name]-[hash][extname]';
                        }

                        // CSS files
                        if (/\.css$/i.test(name)) {
                            return 'css/[name]-[hash][extname]';
                        }

                        // Media files (audio/video)
                        if (/\.(mp4|webm|ogg|mp3|wav|flac|aac)$/i.test(name)) {
                            return 'media/[name]-[hash][extname]';
                        }

                        // Documents
                        if (/\.(pdf|doc|docx|xls|xlsx|ppt|pptx)$/i.test(name)) {
                            return 'documents/[name]-[hash][extname]';
                        }

                        // Default: other assets
                        return 'assets/[name]-[hash][extname]';
                    },

                    // Organize JS chunks
                    chunkFileNames: 'js/[name]-[hash].js',
                    entryFileNames: 'js/[name]-[hash].js',
                },
            },

            // Optional: Optimize build
            chunkSizeWarningLimit: 1000, // Adjust based on your needs

            // Optional: Enable/disable minification
            minify: 'esbuild',

            // Optional: Source maps for production debugging
            sourcemap: false, // Set to true if you need source maps in production
        },
        resolve: {
            alias: {
                'vue': 'vue/dist/vue.esm-bundler.js',
            },
        },
    };
});
