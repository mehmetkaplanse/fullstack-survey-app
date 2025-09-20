import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import pinia from './store/pinia.js'
import router from './router/index.js'
import Vue3Toastify, { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import './bootstrap.js';

const app = createApp(App)
app.use(router)
app.use(pinia)
app.use(Vue3Toastify, {
  autoClose: 3000,
  position: 'top-right',
  theme: 'dark',
  closeOnClick: true,
  pauseOnHover: true,
  hideProgressBar: false,
  newestOnTop: true,
  rtl: false
})

app.config.globalProperties.$toast = toast

app.mount('#app')
