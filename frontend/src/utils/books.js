export const BOOK_STATUSES = [
  { value: 'available', label: 'Tersedia', tone: 'success' },
  { value: 'borrowed', label: 'Dipinjam', tone: 'warning' },
]

/**
 * Menyamakan status dari berbagai variasi penulisan backend
 * (mis. "Tersedia", "available", "dipinjam", "borrowed").
 */
export function normalizeBookStatus(status) {
  const value = String(status ?? '').trim().toLowerCase()

  if (['available', 'tersedia', 'aktif', 'in_stock', 'in stock'].includes(value)) {
    return 'available'
  }
  if (['borrowed', 'dipinjam', 'unavailable', 'out', 'out_of_stock'].includes(value)) {
    return 'borrowed'
  }

  return value || 'available'
}

export function getStatusMeta(status) {
  const value = normalizeBookStatus(status)
  const found = BOOK_STATUSES.find((item) => item.value === value)
  if (found) return found

  return {
    value,
    label: value ? value.charAt(0).toUpperCase() + value.slice(1) : 'Tidak diketahui',
    tone: 'neutral',
  }
}

/**
 * Backend dapat membalas array langsung, { data: [...] },
 * paginator Laravel { data: [...], current_page, last_page },
 * atau resource yang membungkus paginator { data: { data: [...] } }.
 */
export function extractBookCollection(payload) {
  if (Array.isArray(payload)) {
    return { items: payload, meta: null }
  }

  if (payload && typeof payload === 'object') {
    if (Array.isArray(payload.data)) {
      return { items: payload.data, meta: buildMeta(payload, payload.data.length) }
    }

    if (payload.data && Array.isArray(payload.data.data)) {
      // Utamakan meta di level terluar jika ada.
      const metaSource =
        payload.meta && typeof payload.meta === 'object' ? payload : payload.data
      const items = payload.data.data

      return { items, meta: buildMeta(metaSource, items.length) }
    }
  }

  return { items: [], meta: null }
}

function buildMeta(source, fallbackCount) {
  const meta =
    source?.meta && typeof source.meta === 'object' ? source.meta : source
  const lastPage = Number(meta?.last_page ?? meta?.lastPage ?? 1)

  return {
    currentPage: Number(meta?.current_page ?? meta?.currentPage ?? 1) || 1,
    lastPage: Number.isFinite(lastPage) && lastPage > 0 ? lastPage : 1,
    total: Number(meta?.total ?? fallbackCount) || fallbackCount,
  }
}

/**
 * Menjaga bentuk data buku tetap konsisten di seluruh UI.
 */
export function normalizeBook(raw = {}) {
  return {
    ...raw,
    id: raw.id ?? raw.book_id ?? raw.uuid ?? null,
    title: String(raw.title ?? ''),
    author: String(raw.author ?? ''),
    isbn: String(raw.isbn ?? ''),
    category: String(raw.category ?? ''),
    status: normalizeBookStatus(raw.status),
  }
}

export function matchesSearch(book, keyword) {
  const q = String(keyword ?? '').trim().toLowerCase()
  if (!q) return true

  return [book.title, book.author, book.isbn, book.category]
    .join(' ')
    .toLowerCase()
    .includes(q)
}
