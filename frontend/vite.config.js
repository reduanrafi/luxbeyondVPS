import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'
import { fileURLToPath } from 'url'
// Prerender disabled for local build (requires Chrome/Puppeteer)
// import Prerender from '@prerenderer/rollup-plugin'
// import PuppeteerRenderer from '@prerenderer/renderer-puppeteer'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)

export default defineConfig(async ({ mode }) => {
  const env = loadEnv(mode, process.cwd())

  return {
    plugins: [
      vue(),
      // Prerender disabled — re-enable on server with Chrome available
    ],

    resolve: {
      alias: {
        '@': path.resolve(__dirname, 'src'),
        '@components': path.resolve(__dirname, 'src/components'),
        '@icons': path.resolve(__dirname, 'src/components/icons'),
        '@views': path.resolve(__dirname, 'src/views'),
      },
    },
  }
})