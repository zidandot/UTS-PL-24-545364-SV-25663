import { createPinia } from 'pinia'

/**
 * Instance Pinia dibuat di modul terpisah agar bisa dipakai di luar komponen,
 * misalnya pada Navigation Guard Vue Router dan handler 401 di main.js.
 */
export const pinia = createPinia()

export default pinia
