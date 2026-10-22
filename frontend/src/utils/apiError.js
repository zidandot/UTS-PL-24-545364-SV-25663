/**
 * Mengubah error Axios menjadi pesan yang ramah untuk ditampilkan.
 */
export function extractErrorMessage(
  error,
  fallback = 'Terjadi kesalahan. Silakan coba lagi.',
) {
  if (!error) return fallback

  if (error.response) {
    const { data, status } = error.response

    // Laravel validation error: { message, errors: { field: [msg] } }
    if (data?.errors && typeof data.errors === 'object') {
      const first = Object.values(data.errors).flat().filter(Boolean)[0]
      if (first) return first
    }

    if (typeof data === 'string' && data.trim()) return data
    if (data?.message) return data.message
    if (data?.error) return data.error

    if (status === 401) return 'Sesi Anda tidak valid. Silakan login kembali.'
    if (status === 403) return 'Anda tidak memiliki akses untuk tindakan ini.'
    if (status === 404) return 'Data yang diminta tidak ditemukan.'
    if (status === 429) return 'Terlalu banyak permintaan. Coba lagi beberapa saat.'
    if (status >= 500) return 'Server sedang bermasalah. Silakan coba lagi nanti.'
  }

  if (error.request) {
    return 'Tidak dapat terhubung ke server. Pastikan API Laravel berjalan dan URL di .env sudah benar.'
  }

  return error.message || fallback
}

/**
 * Mengambil error validasi per field (422) dari Laravel.
 * Hasil: { title: 'Judul wajib diisi.', isbn: '...' }
 */
export function extractFieldErrors(error) {
  const errors = error?.response?.data?.errors
  if (!errors || typeof errors !== 'object' || Array.isArray(errors)) return {}

  return Object.fromEntries(
    Object.entries(errors).map(([field, messages]) => [
      field,
      Array.isArray(messages) ? String(messages[0]) : String(messages),
    ]),
  )
}
