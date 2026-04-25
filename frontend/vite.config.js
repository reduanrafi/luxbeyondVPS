import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'
import { fileURLToPath } from 'url'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)

export default defineConfig(async ({ mode }) => {
  const env = loadEnv(mode, process.cwd())

  return {
    plugins: [
      vue(),
    ],

    resolve: {
      alias: {
        '@': path.resolve(__dirname, 'src'),
        '@components': path.resolve(__dirname, 'src/components'),
        '@icons': path.resolve(__dirname, 'src/components/icons'),
        '@views': path.resolve(__dirname, 'src/views'),
      },
    },

    build: {
      // Raise warning limit to suppress the warning (we split manually below)
      chunkSizeWarningLimit: 700,

      rollupOptions: {
        output: {
          // Split vendor libraries into separate cacheable chunks
          // Browsers can cache these independently — they rarely change
          manualChunks: {
            // Vue core + router + pinia in one chunk
            'vendor-vue': ['vue', 'vue-router', 'pinia'],
            // Axios isolated so it caches separately
            'vendor-axios': ['axios'],
            // Lucide icons are large — isolate them
            'vendor-icons': ['lucide-vue-next'],
            // Swiper slider library
            'vendor-swiper': ['swiper'],
            // Chart.js
            'vendor-charts': ['chart.js', 'vue-chartjs'],
            // Toast notifications
            'vendor-toast': ['vue-toastification'],
          },
        },
      },
    },
  }
})