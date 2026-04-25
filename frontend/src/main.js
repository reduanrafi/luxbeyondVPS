import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css';

import App from './App.vue'
import router from './router'
import './style.css'
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";

const pinia = createPinia()
const app = createApp(App)

app.component('QuillEditor', QuillEditor)
app.use(pinia)
app.use(router)
app.use(Toast, {
    transition: "Vue-Toastification__bounce",
    maxToasts: 20,
    newestOnTop: true
});

// Kick off all critical data fetches in PARALLEL immediately on boot
// This eliminates the waterfall where each component waits to mount before fetching
import { useSettingsStore } from './stores/settings';
import { useProductStore } from './stores/product';
import { useCmsStore } from './stores/cms';

const settingsStore = useSettingsStore();
const productStore = useProductStore();
const cmsStore = useCmsStore();

// Fire all parallel — do not await, let them resolve in the background
Promise.all([
    settingsStore.fetchSettings(),
    productStore.fetchFeatured(),
    productStore.fetchCategories(),
    productStore.fetchBrands(),
    cmsStore.fetchPage('hero-banner'),
]).catch(() => {}); // Silent fail — each store handles its own errors

app.mount('#app')

// Dispatch prerender event for Puppeteer
document.dispatchEvent(new Event('prerender-ready'))
