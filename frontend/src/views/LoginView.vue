<script setup>
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { extractErrorMessage, extractFieldErrors } from '@/utils/apiError'
import AppLogo from '@/components/AppLogo.vue'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const errors = ref({})
const errorMessage = ref('')

const notice = computed(() => {
  if (route.query.loggedOut === '1') {
    return {
      type: 'success',
      text: 'Anda berhasil keluar. Sampai jumpa kembali!',
    }
  }
  if (route.query.reason === 'session-expired') {
    return {
      type: 'info',
      text: 'Sesi Anda telah berakhir. Silakan login kembali untuk melanjutkan.',
    }
  }
  return null
})

/*
 * Hanya izinkan redirect internal (mencegah open redirect seperti //evil.com).
 */
const redirectTarget = computed(() => {
  const target = route.query.redirect
  if (typeof target === 'string' && target.startsWith('/') && !target.startsWith('//')) {
    return target
  }
  return '/dashboard'
})

function clearError(field) {
  if (!errors.value[field]) return
  const next = { ...errors.value }
  delete next[field]
  errors.value = next
}

async function handleLogin() {
  errorMessage.value = ''

  const nextErrors = {}
  const cleanEmail = email.value.trim()

  if (!cleanEmail) nextErrors.email = 'Email wajib diisi.'
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(cleanEmail)) {
    nextErrors.email = 'Format email tidak valid.'
  }

  if (!password.value) nextErrors.password = 'Password wajib diisi.'

  errors.value = nextErrors
  if (Object.keys(nextErrors).length > 0) return

  try {
    await auth.login({ email: cleanEmail, password: password.value })
    await router.replace(redirectTarget.value)
  } catch (error) {
    if (error?.response?.status === 401) {
      errorMessage.value = 'Email atau password salah. Silakan periksa kembali.'
    } else {
      errorMessage.value = extractErrorMessage(
        error,
        'Login gagal. Silakan coba lagi.',
      )
    }
    errors.value = extractFieldErrors(error)
  }
}
</script>

<template>
  <div class="auth-page">
    <section class="auth-showcase">
      <AppLogo tone="light" />

      <div class="auth-showcase__content">
        <p class="eyebrow eyebrow--light">Sistem Perpustakaan</p>
        <h1>Kelola koleksi buku dalam satu tempat.</h1>
        <p class="auth-showcase__lead">
          Catat, perbarui, dan pantau status setiap buku dengan alur kerja yang
          sederhana dan rapi.
        </p>

        <ul class="auth-features">
          <li>
            <span class="auth-features__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H19v15H6.5A2.5 2.5 0 0 0 4 20.5v-15Z" />
                <path d="M4 20.5A2.5 2.5 0 0 1 6.5 18H19" />
              </svg>
            </span>
            Kelola data buku secara terpusat
          </li>
          <li>
            <span class="auth-features__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                <path d="m9 12 2 2 4-4" />
              </svg>
            </span>
            Autentikasi Bearer Token yang aman
          </li>
          <li>
            <span class="auth-features__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 20V10M18 20V4M6 20v-4" />
              </svg>
            </span>
            Pantau status ketersediaan real-time
          </li>
        </ul>
      </div>

      <p class="auth-showcase__foot">
        &copy; {{ new Date().getFullYear() }} Pustaka &middot; Sistem Perpustakaan Digital
      </p>
    </section>

    <section class="auth-form-side">
      <div class="auth-card">
        <div class="auth-mobile-logo">
          <AppLogo />
        </div>

        <span class="auth-badge">Area Anggota</span>
        <h2>Selamat datang kembali</h2>
        <p class="auth-card__subtitle">
          Masuk untuk mengelola data koleksi perpustakaan.
        </p>

        <div v-if="notice" class="alert" :class="`alert--${notice.type}`" role="status">
          <svg class="alert__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="9" />
            <path d="m8.5 12 2.5 2.5 4.5-5" />
          </svg>
          <span>{{ notice.text }}</span>
        </div>

        <div v-if="errorMessage" class="alert alert--danger" role="alert">
          <svg class="alert__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <circle cx="12" cy="12" r="9" />
            <path d="M12 8v5M12 16h.01" />
          </svg>
          <span>{{ errorMessage }}</span>
        </div>

        <form novalidate @submit.prevent="handleLogin">
          <div class="form-field" :class="{ 'has-error': errors.email }">
            <label class="form-label" for="login-email">Email</label>
            <div class="input-group">
              <span class="input-group__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="5" width="18" height="14" rx="2.5" />
                  <path d="m3.5 7 8.5 6 8.5-6" />
                </svg>
              </span>
              <input
                id="login-email"
                v-model="email"
                class="input"
                type="email"
                name="email"
                placeholder="nama@email.com"
                autocomplete="email"
                autofocus
                :disabled="auth.loading"
                @input="clearError('email')"
              />
            </div>
            <p v-if="errors.email" class="field-error">{{ errors.email }}</p>
          </div>

          <div class="form-field" :class="{ 'has-error': errors.password }">
            <label class="form-label" for="login-password">Password</label>
            <div class="input-group">
              <span class="input-group__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="4" y="10" width="16" height="11" rx="2.5" />
                  <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                  <path d="M12 14.5v2" />
                </svg>
              </span>
              <input
                id="login-password"
                v-model="password"
                class="input input--with-action"
                :type="showPassword ? 'text' : 'password'"
                name="password"
                placeholder="Masukkan password"
                autocomplete="current-password"
                :disabled="auth.loading"
                @input="clearError('password')"
              />
              <button
                type="button"
                class="input-group__action"
                :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                @click="showPassword = !showPassword"
              >
                <svg v-if="showPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M3 3l18 18" />
                  <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8" />
                  <path d="M9.9 5.2A10.5 10.5 0 0 1 12 5c5 0 9 4.5 9 7 0 .9-.6 2.1-1.6 3.2M6.2 6.2C4.2 7.6 3 9.6 3 12c0 2.5 4 7 9 7 1.3 0 2.5-.3 3.6-.8" />
                </svg>
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M3 12c0-2.5 4-7 9-7s9 4.5 9 7-4 7-9 7-9-4.5-9-7Z" />
                  <circle cx="12" cy="12" r="2.7" />
                </svg>
              </button>
            </div>
            <p v-if="errors.password" class="field-error">{{ errors.password }}</p>
          </div>

          <button type="submit" class="btn btn--primary btn--block auth-submit" :disabled="auth.loading">
            <span v-if="auth.loading" class="spinner" aria-hidden="true"></span>
            <span>{{ auth.loading ? 'Memproses...' : 'Masuk ke Dashboard' }}</span>
            <svg v-if="!auth.loading" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
          </button>
        </form>

        <p class="auth-hint">
          Gunakan akun yang sudah terdaftar pada API perpustakaan Anda.
        </p>
      </div>
    </section>
  </div>
</template>
