<script setup>
import { computed } from 'vue'
import { getStatusMeta } from '@/utils/books'

const props = defineProps({
  status: {
    type: String,
    default: 'available',
  },
})

const meta = computed(() => getStatusMeta(props.status))
</script>

<template>
  <span class="status-badge" :class="`status-badge--${meta.tone}`">
    <span class="status-badge__dot" aria-hidden="true"></span>
    {{ meta.label }}
  </span>
</template>
