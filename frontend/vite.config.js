import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'
import { fileURLToPath } from 'url'
import Prerender from '@prerenderer/rollup-plugin'
import PuppeteerRenderer from '@prerenderer/renderer-puppeteer'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)

export default defineConfig(async ({ mode }) => {
  const env = loadEnv(mode, process.cwd())
  const API_URL = env.VITE_API_URL

  let productRoutes = []
  let blogRoutes = []

  try {
    // 🔥 Fetch products
    const productRes = await fetch(`${API_URL}/products`)
    const productJson = await productRes.json()

    productRoutes = productJson.data
      .slice(0, 50) // ⚠️ limit if needed
      .map(item => `/shop/${item.slug}`)

    // 🔥 Fetch blogs
    const blogRes = await fetch(`${API_URL}/blogs`)
    const blogJson = await blogRes.json()

    blogRoutes = blogJson.data
      .slice(0, 50)
      .map(item => `/blogs/${item.slug}`)

    console.log('Products:', productRoutes.length)
    console.log('Blogs:', blogRoutes.length)

  } catch (e) {
    console.warn('Prerender fetch failed:', e)
  }

  return {
    plugins: [
      vue(),
      Prerender({
        staticDir: path.resolve(__dirname, 'dist'),
        routes: [
          '/',
          '/shop',
          '/blogs',
          '/request-product',
          ...productRoutes,
          ...blogRoutes, // ✅ add blogs here
        ],
        renderer: new PuppeteerRenderer({
          headless: true,
          renderAfterDocumentEvent: 'prerender-ready',
        }),
      }),
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