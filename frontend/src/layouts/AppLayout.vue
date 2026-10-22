<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppLogo from '@/components/AppLogo.vue'

const auth = useAuthStore()
const router = useRouter()
const loggingOut = ref(false)

async function handleLogout() {
  if (loggingOut.value) return

  loggingOut.value = true
  try {
    await auth.logout()
    await router.replace({ name: 'login', query: { loggedOut: '1' } })
  } finally {
    loggingOut.value = false
  }
}
</script>

<template>
  <div class="app-shell">
    <header class="app-header">
      <div class="app-header__inner">
        <router-link :to="{ name: 'dashboard' }" class="logo-link" aria-label="Ke dashboard">
          <AppLogo />
        </router-link>

        <nav class="app-nav" aria-label="Navigasi utama">
          <router-link :to="{ name: 'dashboard' }" class="app-nav__link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <rect x="3" y="3" width="7" height="7" rx="1.5" />
              <rect x="14" y="3" width="7" height="7" rx="1.5" />
              <rect x="3" y="14" width="7" height="7" rx="1.5" />
              <rect x="14" y="14" width="7" height="7" rx="1.5" />
            </svg>
            Dashboard
          </router-link>
        </nav>

        <div class="app-header__spacer"></div>

        <div class="app-user" :title="auth.userEmail || auth.userName">
          <div class="avatar" aria-hidden="true">{{ auth.userInitials }}</div>
          <div class="app-user__meta">
            <span class="app-user__name">{{ auth.userName }}</span>
            <span v-if="auth.userEmail" class="app-user__email">{{ auth.userEmail }}</span>
          </div>
        </div>

        <button
          type="button"
          class="btn btn--secondary btn--sm logout-btn"
          :disabled="loggingOut"
          @click="handleLogout"
        >
          <span v-if="loggingOut" class="spinner spinner--dark" aria-hidden="true"></span>
          <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <path d="m16 17 5-5-5-5M21 12H9" />
          </svg>
          <span>{{ loggingOut ? 'Keluar...' : 'Keluar' }}</span>
        </button>
      </div>
    </header>

    <main class="app-main">
      <router-view />
    </main>

    <footer class="app-footer">
      Sistem Perpustakaan &middot; Data tersinkron dengan REST API Laravel
    </footer>
  </div>
</template>
