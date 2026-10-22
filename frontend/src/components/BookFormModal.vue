<script setup>
import { computed, nextTick, reactive, ref, watch } from 'vue'
import BaseModal from './BaseModal.vue'
import { BOOK_STATUSES } from '@/utils/books'

const props = defineProps({
  open: { type: Boolean, default: false },
  book: { type: Object, default: null },
  saving: { type: Boolean, default: false },
  serverError: { type: String, default: '' },
  fieldErrors: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['close', 'submit'])

const form = reactive({
  title: '',
  author: '',
  isbn: '',
  category: '',
  status: 'available',
})

const errors = ref({})
const titleInput = ref(null)

const isEdit = computed(() => Boolean(props.book))

const categorySuggestions = [
  'Fiksi',
  'Non-Fiksi',
  'Sains',
  'Teknologi',
  'Sejarah',
  'Biografi',
  'Referensi',
  'Anak',
]

function resetForm() {
  form.title = props.book?.title ?? ''
  form.author = props.book?.author ?? ''
  form.isbn = props.book?.isbn ?? ''
  form.category = props.book?.category ?? ''
  form.status = props.book?.status === 'borrowed' ? 'borrowed' : 'available'
  errors.value = {}
}

watch(
  () => props.open,
  (isOpen) => {
    if (!isOpen) return
    resetForm()
    nextTick(() => titleInput.value?.focus())
  },
)

function fieldError(field) {
  return errors.value[field] || props.fieldErrors?.[field] || ''
}

function clearFieldError(field) {
  if (!errors.value[field]) return
  const next = { ...errors.value }
  delete next[field]
  errors.value = next
}

function validate() {
  const next = {}
  const title = form.title.trim()
  const isbn = form.isbn.trim()

  if (!title) next.title = 'Judul buku wajib diisi.'
  else if (title.length > 255) next.title = 'Judul maksimal 255 karakter.'

  if (!form.author.trim()) next.author = 'Nama penulis wajib diisi.'
  else if (form.author.trim().length > 255) next.author = 'Nama penulis maksimal 255 karakter.'

  if (isbn.length > 20) next.isbn = 'ISBN maksimal 20 karakter.'
  if (form.category.trim().length > 100) {
    next.category = 'Kategori maksimal 100 karakter.'
  }

  if (!BOOK_STATUSES.some((status) => status.value === form.status)) {
    next.status = 'Status tidak valid.'
  }

  errors.value = next
  return Object.keys(next).length === 0
}

function handleSubmit() {
  if (props.saving || !validate()) return

  emit('submit', {
    title: form.title.trim(),
    author: form.author.trim(),
    isbn: form.isbn.trim() || null,
    category: form.category.trim() || null,
    status: form.status,
  })
}

function handleClose() {
  if (!props.saving) emit('close')
}
</script>

<template>
  <BaseModal
    :open="open"
    :title="isEdit ? 'Ubah Data Buku' : 'Tambah Buku Baru'"
    :description="
      isEdit
        ? 'Perbarui detail koleksi atau ubah status peminjaman.'
        : 'Lengkapi detail buku untuk menambahkannya ke koleksi.'
    "
    @close="handleClose"
  >
    <div v-if="serverError" class="alert alert--danger" role="alert">
      <svg class="alert__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
        <circle cx="12" cy="12" r="9" />
        <path d="M12 8v5M12 16h.01" />
      </svg>
      <span>{{ serverError }}</span>
    </div>

    <form id="book-form" class="form-grid" novalidate @submit.prevent="handleSubmit">
      <div class="form-field form-field--full" :class="{ 'has-error': fieldError('title') }">
        <label class="form-label" for="book-title">
          Judul Buku <span class="req">*</span>
        </label>
        <input
          id="book-title"
          ref="titleInput"
          v-model="form.title"
          class="input"
          type="text"
          placeholder="Contoh: Bumi Manusia"
          maxlength="255"
          autocomplete="off"
          :disabled="saving"
          @input="clearFieldError('title')"
        />
        <p v-if="fieldError('title')" class="field-error">{{ fieldError('title') }}</p>
      </div>

      <div class="form-field" :class="{ 'has-error': fieldError('author') }">
        <label class="form-label" for="book-author">
          Penulis <span class="req">*</span>
        </label>
        <input
          id="book-author"
          v-model="form.author"
          class="input"
          type="text"
          placeholder="Contoh: Pramoedya Ananta Toer"
          maxlength="255"
          autocomplete="off"
          :disabled="saving"
          @input="clearFieldError('author')"
        />
        <p v-if="fieldError('author')" class="field-error">{{ fieldError('author') }}</p>
      </div>

      <div class="form-field" :class="{ 'has-error': fieldError('isbn') }">
        <label class="form-label" for="book-isbn">
          ISBN <span class="optional">(opsional)</span>
        </label>
        <input
          id="book-isbn"
          v-model="form.isbn"
          class="input input--mono"
          type="text"
          placeholder="978-979-97312-3-2"
          maxlength="20"
          autocomplete="off"
          :disabled="saving"
          @input="clearFieldError('isbn')"
        />
        <p v-if="fieldError('isbn')" class="field-error">{{ fieldError('isbn') }}</p>
      </div>

      <div class="form-field" :class="{ 'has-error': fieldError('category') }">
        <label class="form-label" for="book-category">
          Kategori <span class="optional">(opsional)</span>
        </label>
        <input
          id="book-category"
          v-model="form.category"
          class="input"
          type="text"
          list="category-suggestions"
          placeholder="Contoh: Fiksi"
          maxlength="100"
          autocomplete="off"
          :disabled="saving"
          @input="clearFieldError('category')"
        />
        <datalist id="category-suggestions">
          <option v-for="category in categorySuggestions" :key="category" :value="category" />
        </datalist>
        <p v-if="fieldError('category')" class="field-error">{{ fieldError('category') }}</p>
      </div>

      <fieldset class="status-field form-field--full" :class="{ 'has-error': fieldError('status') }">
        <legend class="form-label">Status <span class="req">*</span></legend>
        <div class="status-options">
          <label
            v-for="status in BOOK_STATUSES"
            :key="status.value"
            class="status-option"
            :class="[
              `status-option--${status.tone}`,
              { 'is-checked': form.status === status.value },
            ]"
          >
            <input
              v-model="form.status"
              type="radio"
              name="book-status"
              :value="status.value"
              :disabled="saving"
              @change="clearFieldError('status')"
            />
            <span class="status-option__dot" aria-hidden="true"></span>
            <span>
              <span class="status-option__label">{{ status.label }}</span>
              <span class="status-option__desc">
                {{ status.value === 'available' ? 'Siap dipinjam anggota' : 'Sedang berada di peminjam' }}
              </span>
            </span>
          </label>
        </div>
        <p v-if="fieldError('status')" class="field-error">{{ fieldError('status') }}</p>
      </fieldset>
    </form>

    <template #footer>
      <button type="button" class="btn btn--secondary" :disabled="saving" @click="handleClose">
        Batal
      </button>
      <button type="submit" form="book-form" class="btn btn--primary" :disabled="saving">
        <span v-if="saving" class="spinner" aria-hidden="true"></span>
        {{ saving ? 'Menyimpan...' : isEdit ? 'Simpan Perubahan' : 'Tambah Buku' }}
      </button>
    </template>
  </BaseModal>
</template>
