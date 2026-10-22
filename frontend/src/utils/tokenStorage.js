const TOKEN_KEY = 'perpustakaan.token'
const USER_KEY = 'perpustakaan.user'

/**
 * Semua akses localStorage dipusatkan di sini agar key konsisten
 * antara Pinia store, Axios interceptor, dan router guard.
 */
export function getToken() {
  try {
    return localStorage.getItem(TOKEN_KEY)
  } catch {
    return null
  }
}

export function setToken(token) {
  try {
    localStorage.setItem(TOKEN_KEY, token)
  } catch {
    // localStorage bisa diblokir (mode privat); token tetap tersimpan di Pinia.
  }
}

export function removeToken() {
  try {
    localStorage.removeItem(TOKEN_KEY)
  } catch {
    // abaikan
  }
}

export function getStoredUser() {
  try {
    const raw = localStorage.getItem(USER_KEY)
    return raw ? JSON.parse(raw) : null
  } catch {
    return null
  }
}

export function setStoredUser(user) {
  try {
    if (user) {
      localStorage.setItem(USER_KEY, JSON.stringify(user))
    } else {
      localStorage.removeItem(USER_KEY)
    }
  } catch {
    // abaikan
  }
}

export function clearAuthStorage() {
  removeToken()
  try {
    localStorage.removeItem(USER_KEY)
  } catch {
    // abaikan
  }
}
