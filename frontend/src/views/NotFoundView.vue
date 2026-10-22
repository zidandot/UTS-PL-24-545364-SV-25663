<script setup>
import AppLogo from '@/components/AppLogo.vue'
</script>

<template>
  <div class="notfound-page">
    <div class="notfound-card">
      <AppLogo />
      <p class="notfound-code">404</p>
      <h1>Halaman tidak ditemukan</h1>
      <p class="notfound-text">
        Tautan yang Anda buka mungkin salah atau halaman sudah dipindahkan.
      </p>
      <router-link :to="{ name: 'dashboard' }" class="btn btn--primary">
        Kembali ke Dashboard
      </router-link>
    </div>
  </div>
</template>
