import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'
import { fileURLToPath } from 'url'
import Prerender from '@prerenderer/rollup-plugin'
import PuppeteerRenderer from '@prerenderer/renderer-puppeteer'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue(), 

    prerender({
      staticDir: path.resolve(__dirname, 'dist'),
      routes: ['/', '/shop', '/request-product', '/blogs'], // list top routes
      renderer: new PuppeteerRenderer({
        headless: true,
        renderAfterDocumentEvent: 'prerender-ready', // we will trigger this from Vue
      }),
    }),
  ],
  // START FIX: Wrap alias inside 'resolve'
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'src'),
      '@components': path.resolve(__dirname, 'src/components'),
      '@icons': path.resolve(__dirname, 'src/components/icons'),
      '@views': path.resolve(__dirname, 'src/views'),
    },
  },
  server: {
    proxy: {
      '/storage': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
        secure: false,
      }
    }
  }
  // END FIX
})