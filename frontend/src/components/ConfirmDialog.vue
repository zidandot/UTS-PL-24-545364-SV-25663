<script setup>
import BaseModal from './BaseModal.vue'

const props = defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: 'Hapus buku ini?' },
  bookTitle: { type: String, default: '' },
  loading: { type: Boolean, default: false },
  error: { type: String, default: '' },
})

const emit = defineEmits(['cancel', 'confirm'])

function handleClose() {
  if (!props.loading) emit('cancel')
}
</script>

<template>
  <BaseModal :open="open" :title="title" size="sm" @close="handleClose">
    <div class="confirm-icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
        <path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
        <path d="M10 11v5M14 11v5" />
      </svg>
    </div>

    <p class="confirm-text">
      Buku
      <strong v-if="bookTitle">“{{ bookTitle }}”</strong>
      <span v-else>ini</span>
      akan dihapus permanen dari koleksi. Tindakan ini tidak dapat dibatalkan.
    </p>

    <div v-if="error" class="alert alert--danger confirm-alert" role="alert">
      <svg class="alert__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
        <circle cx="12" cy="12" r="9" />
        <path d="M12 8v5M12 16h.01" />
      </svg>
      <span>{{ error }}</span>
    </div>

    <template #footer>
      <button type="button" class="btn btn--secondary" :disabled="loading" @click="handleClose">
        Batal
      </button>
      <button type="button" class="btn btn--danger" :disabled="loading" @click="emit('confirm')">
        <span v-if="loading" class="spinner" aria-hidden="true"></span>
        {{ loading ? 'Menghapus...' : 'Ya, Hapus' }}
      </button>
    </template>
  </BaseModal>
</template>
