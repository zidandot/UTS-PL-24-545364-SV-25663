<script setup>
defineProps({
  tone: {
    type: String,
    default: 'dark',
    validator: (value) => ['dark', 'light'].includes(value),
  },
})
</script>

<template>
  <span class="app-logo" :class="{ 'app-logo--light': tone === 'light' }">
    <span class="app-logo__mark" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
        <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H19v15H6.5A2.5 2.5 0 0 0 4 20.5v-15Z" />
        <path d="M4 20.5A2.5 2.5 0 0 1 6.5 18H19" />
        <path d="M8.5 7.5h6M8.5 11h4" />
      </svg>
    </span>
    <span class="app-logo__text">Pustaka</span>
  </span>
</template>
