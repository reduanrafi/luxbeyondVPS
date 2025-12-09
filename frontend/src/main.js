import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css';

import App from './App.vue'
import router from './router'
import './style.css'

const app = createApp(App)

app.component('QuillEditor', QuillEditor)
app.use(createPinia())
app.use(router)
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";

app.use(Toast, {
    transition: "Vue-Toastification__bounce",
    maxToasts: 20,
    newestOnTop: true
});

app.mount('#app')
