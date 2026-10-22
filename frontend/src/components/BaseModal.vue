<script setup>
import { onBeforeUnmount, watch } from 'vue'

const props = defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: '' },
  description: { type: String, default: '' },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value),
  },
  closeOnBackdrop: { type: Boolean, default: true },
})

const emit = defineEmits(['close'])

const titleId = `modal-title-${Math.random().toString(36).slice(2, 9)}`
let previousOverflow = ''

function requestClose() {
  emit('close')
}

function handleBackdrop() {
  if (props.closeOnBackdrop) requestClose()
}

function handleKeydown(event) {
  if (event.key === 'Escape') requestClose()
}

watch(
  () => props.open,
  (isOpen) => {
    if (typeof document === 'undefined') return

    if (isOpen) {
      previousOverflow = document.body.style.overflow
      document.body.style.overflow = 'hidden'
      document.addEventListener('keydown', handleKeydown)
    } else {
      document.body.style.overflow = previousOverflow || ''
      document.removeEventListener('keydown', handleKeydown)
    }
  },
  { immediate: true },
)

onBeforeUnmount(() => {
  document.body.style.overflow = previousOverflow || ''
  document.removeEventListener('keydown', handleKeydown)
})
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="open"
        class="modal-backdrop"
        @mousedown.self="handleBackdrop"
      >
        <div
          class="modal-panel"
          :class="`modal-panel--${size}`"
          role="dialog"
          aria-modal="true"
          :aria-labelledby="titleId"
        >
          <header class="modal-head">
            <div>
              <h2 :id="titleId" class="modal-title">{{ title }}</h2>
              <p v-if="description" class="modal-description">{{ description }}</p>
            </div>
            <button
              type="button"
              class="icon-btn modal-close"
              aria-label="Tutup dialog"
              @click="requestClose"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M18 6 6 18M6 6l12 12" />
              </svg>
            </button>
          </header>

          <div class="modal-body">
            <slot />
          </div>

          <footer v-if="$slots.footer" class="modal-foot">
            <slot name="footer" />
          </footer>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
