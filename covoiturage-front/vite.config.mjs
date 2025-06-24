import { fileURLToPath, URL } from 'node:url';

import { PrimeVueResolver } from '@primevue/auto-import-resolver';
import vue from '@vitejs/plugin-vue';
import Components from 'unplugin-vue-components/vite';
import { defineConfig } from 'vite';

// https://vitejs.dev/config/
export default defineConfig({
    define: {
        'process.env': {} // Ajout important pour éviter certaines erreurs avec Pusher
    },
    optimizeDeps: {
        noDiscovery: true,
        include: ['pusher-js', 'laravel-echo', 'leaflet']
    },
    plugins: [
        vue(),
        Components({
            resolvers: [PrimeVueResolver()]
        })
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./src', import.meta.url))
        }
    } ,
    build: {
        rollupOptions: {
          external: ['laravel-echo', 'pusher-js'] // Exclure ces modules du bundling
        }
      }
});
