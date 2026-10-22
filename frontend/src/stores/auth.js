import { defineStore } from 'pinia'
import api from '@/services/api'
import {
  getToken,
  setToken,
  removeToken,
  getStoredUser,
  setStoredUser,
} from '@/utils/tokenStorage'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: getToken(),
    user: getStoredUser(),
    loading: false,
  }),

  getters: {
    isAuthenticated: (state) => Boolean(state.token),
    userName: (state) => state.user?.name || state.user?.username || 'Pengguna',
    userEmail: (state) => state.user?.email || '',
    userInitials: (state) => {
      const source = state.user?.name || state.user?.email || 'P'
      return source
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('')
    },
  },

  actions: {
    /**
     * POST /api/login — menyimpan Bearer Token ke Pinia + localStorage.
     */
    async login(credentials) {
      this.loading = true

      try {
        const response = await api.post('/login', {
          email: credentials.email,
          password: credentials.password,
        })

        const body = response.data ?? {}
        const payload = body.data ?? body
        const token =
          payload.token ??
          payload.access_token ??
          body.token ??
          body.access_token

        if (!token || typeof token !== 'string') {
          throw new Error(
            'Respons login tidak memuat token. Periksa format respons API /login.',
          )
        }

        const user = payload.user ?? body.user ?? null

        this.token = token
        setToken(token)

        if (user) {
          this.user = user
          setStoredUser(user)
        } else {
          // Sebagian API hanya mengembalikan token; ambil profil secara async.
          this.fetchUser().catch(() => {})
        }

        return { token, user }
      } finally {
        this.loading = false
      }
    },

    /** GET /api/me — dipakai untuk melengkapi data profil di navbar. */
    async fetchUser() {
      const response = await api.get('/me')
      const body = response.data ?? {}
      const payload = body.data ?? body
      const user = payload.user ?? payload

      if (user && typeof user === 'object') {
        this.user = user
        setStoredUser(user)
      }

      return user
    },

    /**
     * POST /api/logout — token dicabut di server, lalu penyimpanan lokal
     * dibersihkan. Redirect ke /login dilakukan oleh pemanggil (navbar).
     */
    async logout() {
      try {
        if (this.token) {
          await api.post('/logout', null, { skipAuthRedirect: true })
        }
      } catch {
        // Tetap logout di sisi klien meskipun server tidak dapat dihubungi.
      } finally {
        this.clearAuth()
      }
    },

    clearAuth() {
      this.token = null
      this.user = null
      removeToken()
      setStoredUser(null)
    },
  },
})
