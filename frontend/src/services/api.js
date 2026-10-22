import axios from 'axios'
import { getToken, clearAuthStorage } from '@/utils/tokenStorage'

const baseURL = (import.meta.env.VITE_API_BASE_URL || '').replace(/\/+$/, '')

if (!baseURL) {
  // Gagal cepat dengan pesan jelas daripada request menembak origin Vite.
  console.warn(
    '[API] VITE_API_BASE_URL belum diatur. Salin .env.example menjadi .env lalu restart dev server.',
  )
}

/**
 * Instance Axios tunggal yang dipakai seluruh aplikasi.
 * URL API TIDAK di-hardcode di komponen, melainkan dari .env.
 */
const api = axios.create({
  baseURL,
  timeout: 20000,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

let unauthorizedHandler = null

/** Dipanggil sekali dari main.js untuk menangani sesi kedaluwarsa. */
export function setUnauthorizedHandler(handler) {
  unauthorizedHandler = handler
}

api.interceptors.request.use(
  (config) => {
    const token = getToken()

    // Bearer Token otomatis disisipkan pada setiap request berikutnya.
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }

    return config
  },
  (error) => Promise.reject(error),
)

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status
    const url = error.config?.url || ''
    const isLoginRequest = /\/login\/?$/.test(url)
    const skipRedirect = Boolean(error.config?.skipAuthRedirect)

    // 401 selain saat login → token tidak valid/kedaluwarsa.
    if (status === 401 && !isLoginRequest && !skipRedirect) {
      clearAuthStorage()
      if (typeof unauthorizedHandler === 'function') unauthorizedHandler()
    }

    return Promise.reject(error)
  },
)

export default api
