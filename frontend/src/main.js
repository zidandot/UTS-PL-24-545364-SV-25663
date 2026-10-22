import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { pinia } from './stores'
import { useAuthStore } from './stores/auth'
import { setUnauthorizedHandler } from './services/api'
import './assets/main.css'

const app = createApp(App)

app.use(pinia)
app.use(router)

/*
 * Ketika API membalas 401 (token kedaluwarsa / dicabut), Axios interceptor
 * memanggil handler ini agar state Pinia & localStorage ikut dibersihkan
 * lalu pengguna diarahkan kembali ke halaman login.
 */
setUnauthorizedHandler(() => {
  const auth = useAuthStore(pinia)
  auth.clearAuth()

  const current = router.currentRoute.value
  if (current.name !== 'login') {
    router.replace({
      name: 'login',
      query: { redirect: current.fullPath, reason: 'session-expired' },
    })
  }
})

app.mount('#app')
