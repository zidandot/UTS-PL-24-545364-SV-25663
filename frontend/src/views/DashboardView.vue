<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import BookFormModal from '@/components/BookFormModal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import {
  createBook,
  deleteBook,
  fetchBooks,
  updateBook,
} from '@/services/bookService'
import { useAuthStore } from '@/stores/auth'
import { extractErrorMessage, extractFieldErrors } from '@/utils/apiError'
import { matchesSearch } from '@/utils/books'

const auth = useAuthStore()

const books = ref([])
const loading = ref(false)
const hasLoaded = ref(false)
const loadError = ref('')

const search = ref('')
const statusFilter = ref('all')

const alert = ref(null)
let alertTimer = null

const formOpen = ref(false)
const editingBook = ref(null)
const saving = ref(false)
const formError = ref('')
const fieldErrors = ref({})

const deleteTarget = ref(null)
const deleting = ref(false)
const deleteError = ref('')

const totalBooks = computed(() => books.value.length)
const availableBooks = computed(
  () => books.value.filter((book) => book.status === 'available').length,
)
const borrowedBooks = computed(
  () => books.value.filter((book) => book.status === 'borrowed').length,
)
const categoryCount = computed(
  () =>
    new Set(
      books.value
        .map((book) => book.category.trim().toLowerCase())
        .filter(Boolean),
    ).size,
)
const availablePercent = computed(() =>
  totalBooks.value === 0
    ? 0
    : Math.round((availableBooks.value / totalBooks.value) * 100),
)

const filteredBooks = computed(() =>
  books.value.filter((book) => {
    const matchStatus =
      statusFilter.value === 'all' || book.status === statusFilter.value
    return matchStatus && matchesSearch(book, search.value)
  }),
)

const hasActiveFilters = computed(
  () => search.value.trim() !== '' || statusFilter.value !== 'all',
)

function showAlert(type, message) {
  alert.value = { type, message }
  clearTimeout(alertTimer)
  alertTimer = setTimeout(() => {
    alert.value = null
  }, 5000)
}

async function loadBooks({ silent = false } = {}) {
  if (!silent) loading.value = true
  loadError.value = ''

  try {
    const result = await fetchBooks()
    books.value = result.items
    hasLoaded.value = true
  } catch (error) {
    const message = extractErrorMessage(error, 'Gagal memuat daftar buku.')
    if (books.value.length > 0) {
      showAlert('danger', message)
    } else {
      loadError.value = message
    }
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  search.value = ''
  statusFilter.value = 'all'
}

function openCreate() {
  editingBook.value = null
  formError.value = ''
  fieldErrors.value = {}
  formOpen.value = true
}

function openEdit(book) {
  editingBook.value = book
  formError.value = ''
  fieldErrors.value = {}
  formOpen.value = true
}

function closeForm() {
  if (saving.value) return
  formOpen.value = false
  formError.value = ''
  fieldErrors.value = {}
}

async function handleSave(payload) {
  if (saving.value) return

  saving.value = true
  formError.value = ''
  fieldErrors.value = {}
  const isEditMode = Boolean(editingBook.value)

  try {
    if (isEditMode) {
      await updateBook(editingBook.value.id, payload)
    } else {
      await createBook(payload)
    }

    formOpen.value = false
    showAlert(
      'success',
      isEditMode
        ? `Buku “${payload.title}” berhasil diperbarui.`
        : `Buku “${payload.title}” berhasil ditambahkan.`,
    )
    await loadBooks({ silent: true })
  } catch (error) {
    formError.value = extractErrorMessage(error, 'Gagal menyimpan buku.')
    fieldErrors.value = extractFieldErrors(error)
  } finally {
    saving.value = false
  }
}

function askDelete(book) {
  deleteError.value = ''
  deleteTarget.value = book
}

function cancelDelete() {
  if (deleting.value) return
  deleteTarget.value = null
  deleteError.value = ''
}

async function confirmDelete() {
  if (!deleteTarget.value || deleting.value) return

  const target = deleteTarget.value
  deleting.value = true
  deleteError.value = ''

  try {
    await deleteBook(target.id)
    deleteTarget.value = null
    showAlert('success', `Buku “${target.title}” berhasil dihapus.`)
    await loadBooks({ silent: true })
  } catch (error) {
    deleteError.value = extractErrorMessage(error, 'Gagal menghapus buku.')
  } finally {
    deleting.value = false
  }
}

function hasId(book) {
  return book.id !== null && book.id !== undefined
}

/*
 * Backend hanya mengizinkan pemilik buku untuk mengubah/menghapus.
 * Sembunyikan akses tersebut jika user_id buku bukan milik user login.
 */
function canManage(book) {
  const userId = auth.user?.id
  if (userId === null || userId === undefined) return true
  if (book.user_id === null || book.user_id === undefined) return true
  return Number(book.user_id) === Number(userId)
}

onMounted(() => loadBooks())

onBeforeUnmount(() => clearTimeout(alertTimer))
</script>

<template>
  <div class="dashboard">
    <header class="page-head">
      <div>
        <p class="eyebrow">Dashboard</p>
        <h1>Koleksi Buku</h1>
        <p class="page-subtitle">
          Kelola seluruh data buku perpustakaan dan pantau status peminjamannya.
        </p>
      </div>
      <div class="page-actions">
        <button
          type="button"
          class="btn btn--secondary"
          :disabled="loading"
          @click="loadBooks()"
        >
          <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.9"
            stroke-linecap="round"
            stroke-linejoin="round"
            :class="{ 'is-spinning': loading }"
            aria-hidden="true"
          >
            <path d="M21 12a9 9 0 1 1-2.64-6.36" />
            <path d="M21 3v6h-6" />
          </svg>
          Muat Ulang
        </button>
        <button type="button" class="btn btn--primary" @click="openCreate">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
            <path d="M12 5v14M5 12h14" />
          </svg>
          Tambah Buku
        </button>
      </div>
    </header>

    <section class="stats-grid" aria-label="Ringkasan koleksi">
      <article class="stat-card">
        <span class="stat-icon stat-icon--brand" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H19v15H6.5A2.5 2.5 0 0 0 4 20.5v-15Z" />
            <path d="M4 20.5A2.5 2.5 0 0 1 6.5 18H19" />
          </svg>
        </span>
        <div>
          <p class="stat-card__label">Total Buku</p>
          <p class="stat-card__value">{{ totalBooks }}</p>
          <p class="stat-card__hint">buku terdaftar</p>
        </div>
      </article>

      <article class="stat-card">
        <span class="stat-icon stat-icon--success" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="9" />
            <path d="m8 12 2.5 2.5L16 9" />
          </svg>
        </span>
        <div>
          <p class="stat-card__label">Tersedia</p>
          <p class="stat-card__value">{{ availableBooks }}</p>
          <p class="stat-card__hint">{{ availablePercent }}% dari koleksi</p>
        </div>
      </article>

      <article class="stat-card">
        <span class="stat-icon stat-icon--warning" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 7.5 12 12l9-4.5" />
            <rect x="3" y="4.5" width="18" height="15" rx="3" />
            <path d="M12 12v7.5" />
          </svg>
        </span>
        <div>
          <p class="stat-card__label">Dipinjam</p>
          <p class="stat-card__value">{{ borrowedBooks }}</p>
          <p class="stat-card__hint">
            {{ borrowedBooks > 0 ? 'sedang dipinjam anggota' : 'tidak ada peminjaman' }}
          </p>
        </div>
      </article>

      <article class="stat-card">
        <span class="stat-icon stat-icon--blue" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8Z" />
            <circle cx="7.5" cy="7.5" r="1.2" />
          </svg>
        </span>
        <div>
          <p class="stat-card__label">Kategori</p>
          <p class="stat-card__value">{{ categoryCount }}</p>
          <p class="stat-card__hint">kategori berbeda</p>
        </div>
      </article>
    </section>

    <div v-if="alert" class="alert page-alert" :class="`alert--${alert.type}`" role="status">
      <svg class="alert__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="9" />
        <path v-if="alert.type === 'success'" d="m8 12 2.5 2.5L16 9" />
        <path v-else d="M12 8v5M12 16h.01" />
      </svg>
      <span>{{ alert.message }}</span>
      <button type="button" class="alert__close" aria-label="Tutup notifikasi" @click="alert = null">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <path d="M18 6 6 18M6 6l12 12" />
        </svg>
      </button>
    </div>

    <section class="panel">
      <div class="panel__head">
        <div class="panel__heading">
          <h2 class="panel__title">Daftar Buku</h2>
          <span class="count-pill">{{ filteredBooks.length }}</span>
        </div>

        <div class="toolbar">
          <div class="search-field">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" aria-hidden="true">
              <circle cx="11" cy="11" r="7" />
              <path d="m20 20-3.5-3.5" />
            </svg>
            <input
              v-model="search"
              class="input"
              type="search"
              placeholder="Cari judul, penulis, ISBN..."
              aria-label="Cari buku"
            />
          </div>

          <select v-model="statusFilter" class="select select--compact" aria-label="Filter status buku">
            <option value="all">Semua status</option>
            <option value="available">Tersedia</option>
            <option value="borrowed">Dipinjam</option>
          </select>

          <button
            type="button"
            class="icon-btn refresh-btn"
            :disabled="loading"
            title="Muat ulang data"
            aria-label="Muat ulang data"
            @click="loadBooks()"
          >
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.9"
              stroke-linecap="round"
              stroke-linejoin="round"
              :class="{ 'is-spinning': loading }"
            >
              <path d="M21 12a9 9 0 1 1-2.64-6.36" />
              <path d="M21 3v6h-6" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Skeleton saat pemuatan pertama -->
      <div v-if="loading && !hasLoaded" class="skeleton-list" aria-label="Memuat data buku" aria-busy="true">
        <div v-for="row in 5" :key="row" class="skeleton-row">
          <span class="skeleton skeleton--index"></span>
          <span class="skeleton skeleton--title"></span>
          <span class="skeleton skeleton--text"></span>
          <span class="skeleton skeleton--text"></span>
          <span class="skeleton skeleton--badge"></span>
          <span class="skeleton skeleton--action"></span>
        </div>
      </div>

      <!-- Gagal memuat & belum ada data -->
      <div v-else-if="loadError && books.length === 0" class="empty-state">
        <span class="empty-state__icon empty-state__icon--danger" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
            <circle cx="12" cy="12" r="9" />
            <path d="M12 8v5M12 16h.01" />
          </svg>
        </span>
        <h3>Gagal memuat data</h3>
        <p>{{ loadError }}</p>
        <button type="button" class="btn btn--primary" @click="loadBooks()">
          Coba Lagi
        </button>
      </div>

      <!-- Tidak ada hasil -->
      <div v-else-if="filteredBooks.length === 0" class="empty-state">
        <span class="empty-state__icon" aria-hidden="true">
          <svg v-if="books.length === 0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H19v15H6.5A2.5 2.5 0 0 0 4 20.5v-15Z" />
            <path d="M4 20.5A2.5 2.5 0 0 1 6.5 18H19" />
            <path d="M9 8h6" />
          </svg>
          <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
            <circle cx="11" cy="11" r="7" />
            <path d="m20 20-3.5-3.5M8.5 11h5" />
          </svg>
        </span>
        <template v-if="books.length === 0">
          <h3>Belum ada buku</h3>
          <p>Mulai bangun koleksi perpustakaan dengan menambahkan buku pertama.</p>
          <button type="button" class="btn btn--primary" @click="openCreate">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
              <path d="M12 5v14M5 12h14" />
            </svg>
            Tambah Buku
          </button>
        </template>
        <template v-else>
          <h3>Tidak ada buku yang cocok</h3>
          <p>Coba ubah kata kunci pencarian atau filter status yang dipilih.</p>
          <button type="button" class="btn btn--secondary" @click="resetFilters">
            Reset Filter
          </button>
        </template>
      </div>

      <template v-else>
        <!-- Tabel (desktop) -->
        <div class="table-wrap">
          <table class="book-table">
            <thead>
              <tr>
                <th scope="col" class="col-index">No</th>
                <th scope="col">Judul</th>
                <th scope="col">Penulis</th>
                <th scope="col">ISBN</th>
                <th scope="col">Kategori</th>
                <th scope="col">Status</th>
                <th scope="col" class="col-actions">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(book, index) in filteredBooks" :key="book.id ?? `${book.isbn}-${index}`">
                <td class="col-index">
                  <span class="row-index">{{ index + 1 }}</span>
                </td>
                <td>
                  <span class="cell-title">{{ book.title || 'Tanpa judul' }}</span>
                </td>
                <td>{{ book.author || '—' }}</td>
                <td><span class="isbn">{{ book.isbn || '—' }}</span></td>
                <td>
                  <span class="category-chip">{{ book.category || '—' }}</span>
                </td>
                <td><StatusBadge :status="book.status" /></td>
                <td>
                  <div class="row-actions">
                    <button
                      type="button"
                      class="icon-btn"
                      :title="canManage(book) ? 'Ubah buku' : 'Hanya pemilik buku yang dapat mengubah'"
                      :aria-label="`Ubah buku ${book.title}`"
                      :disabled="!hasId(book) || !canManage(book)"
                      @click="openEdit(book)"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9" />
                        <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z" />
                      </svg>
                    </button>
                    <button
                      type="button"
                      class="icon-btn icon-btn--danger"
                      :title="canManage(book) ? 'Hapus buku' : 'Hanya pemilik buku yang dapat menghapus'"
                      :aria-label="`Hapus buku ${book.title}`"
                      :disabled="!hasId(book) || !canManage(book)"
                      @click="askDelete(book)"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                        <path d="M10 11v5M14 11v5" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Kartu (mobile) -->
        <div class="book-cards">
          <article
            v-for="(book, index) in filteredBooks"
            :key="`card-${book.id ?? `${book.isbn}-${index}`}`"
            class="book-card"
          >
            <div class="book-card__top">
              <div>
                <h3 class="book-card__title">{{ book.title || 'Tanpa judul' }}</h3>
                <p class="book-card__author">{{ book.author || 'Penulis tidak diketahui' }}</p>
              </div>
              <StatusBadge :status="book.status" />
            </div>

            <dl class="book-card__meta">
              <div>
                <dt>ISBN</dt>
                <dd class="isbn">{{ book.isbn || '—' }}</dd>
              </div>
              <div>
                <dt>Kategori</dt>
                <dd>{{ book.category || '—' }}</dd>
              </div>
            </dl>

            <div class="book-card__footer">
              <span class="book-card__number">Buku #{{ index + 1 }}</span>
              <div class="row-actions">
                <button
                  type="button"
                  class="text-btn"
                  :disabled="!hasId(book) || !canManage(book)"
                  @click="openEdit(book)"
                >
                  Ubah
                </button>
                <button
                  type="button"
                  class="text-btn text-btn--danger"
                  :disabled="!hasId(book) || !canManage(book)"
                  @click="askDelete(book)"
                >
                  Hapus
                </button>
              </div>
            </div>
          </article>
        </div>
      </template>

      <footer v-if="!loading && books.length > 0" class="panel__foot">
        <span>
          Menampilkan <strong>{{ filteredBooks.length }}</strong> dari
          <strong>{{ books.length }}</strong> buku
        </span>
        <button v-if="hasActiveFilters" type="button" class="text-btn" @click="resetFilters">
          Reset filter
        </button>
      </footer>
    </section>

    <BookFormModal
      :open="formOpen"
      :book="editingBook"
      :saving="saving"
      :server-error="formError"
      :field-errors="fieldErrors"
      @close="closeForm"
      @submit="handleSave"
    />

    <ConfirmDialog
      :open="Boolean(deleteTarget)"
      :book-title="deleteTarget?.title || ''"
      :loading="deleting"
      :error="deleteError"
      @cancel="cancelDelete"
      @confirm="confirmDelete"
    />
  </div>
</template>
