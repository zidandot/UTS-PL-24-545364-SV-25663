import api from './api'
import { extractBookCollection, normalizeBook } from '@/utils/books'

const MAX_PAGES = 25

/**
 * Mengambil seluruh buku. Jika backend mengembalikan paginator Laravel,
 * semua halaman diambil agar pencarian & statistik mencakup seluruh koleksi.
 */
export async function fetchBooks() {
  const firstResponse = await api.get('/books')
  const firstPage = extractBookCollection(firstResponse.data)

  if (!firstPage.meta || firstPage.meta.lastPage <= 1) {
    return {
      items: firstPage.items.map(normalizeBook),
      total: firstPage.items.length,
    }
  }

  const lastPage = Math.min(firstPage.meta.lastPage, MAX_PAGES)
  const requests = []

  for (let page = 2; page <= lastPage; page += 1) {
    requests.push(api.get('/books', { params: { page } }))
  }

  const responses = await Promise.all(requests)
  const rest = responses.flatMap(
    (response) => extractBookCollection(response.data).items,
  )
  const items = [...firstPage.items, ...rest].map(normalizeBook)

  return {
    items,
    total: firstPage.meta.total || items.length,
  }
}

export function createBook(payload) {
  return api.post('/books', payload)
}

export function updateBook(id, payload) {
  return api.put(`/books/${id}`, payload)
}

export function deleteBook(id) {
  return api.delete(`/books/${id}`)
}
