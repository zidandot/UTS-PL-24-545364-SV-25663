# Pustaka — Frontend Sistem Perpustakaan

Aplikasi frontend **Vue 3 + Vite** untuk REST API *Sistem Perpustakaan / Peminjaman Buku* (Laravel Sanctum). Aplikasi ini menangani login berbasis Bearer Token, proteksi route, serta operasi CRUD buku lengkap dengan pencarian, filter status, dan tampilan responsif.

---

## 1. Fitur

- **Autentikasi**
  - Login via `POST /api/login` (email & password).
  - Bearer Token disimpan di **Pinia** dan **localStorage**.
  - Token otomatis disisipkan ke setiap request melalui **Axios request interceptor**.
  - Logout via `POST /api/logout`, menghapus token, lalu kembali ke `/login`.
  - Respons `401` otomatis membersihkan sesi dan mengarahkan ke login.
- **CRUD Buku**
  - Daftar buku dari `GET /api/books` dalam tabel (desktop) dan kartu (mobile).
  - Tambah buku via modal: `POST /api/books`.
  - Edit data/status buku via modal: `PUT /api/books/{id}`.
  - Hapus buku dengan dialog konfirmasi: `DELETE /api/books/{id}`.
  - Pencarian judul/penulis/ISBN/kategori, filter status, statistik koleksi, dan validasi form (client + server).
- **Arsitektur**
  - Vue Router dengan **navigation guard** (`router.beforeEach`).
  - Semua URL API berasal dari environment variable `VITE_API_BASE_URL`.
  - Pesan error API (termasuk validasi `422` Laravel) ditampilkan ramah di UI.
  - Mendukung respons buku berupa array langsung, `{ data: [...] }`, maupun paginator Laravel.

---

## 2. Teknologi

| Kebutuhan | Paket |
| --- | --- |
| Framework | Vue 3 (`<script setup>`) |
| Build tool | Vite |
| Routing | Vue Router 4 |
| State management | Pinia |
| HTTP client | Axios |
| Styling | CSS murni (design tokens + responsive) |

---

## 3. Struktur Folder

```text
frontend/
├── .env                        # Konfigurasi lokal (tidak di-commit)
├── .env.example                # Contoh konfigurasi environment
├── .gitignore
├── index.html
├── package.json
├── package-lock.json
├── vite.config.js
├── public/
│   └── favicon.svg
└── src/
    ├── App.vue                 # Root component (router-view)
    ├── main.js                 # Bootstrap app, Pinia, Router, handler 401
    ├── assets/
    │   └── main.css            # Design system & seluruh styling
    ├── components/
    │   ├── AppLogo.vue         # Logo aplikasi
    │   ├── BaseModal.vue       # Modal reusable (escape, backdrop, scroll lock)
    │   ├── BookFormModal.vue   # Form tambah/edit buku
    │   ├── ConfirmDialog.vue   # Dialog konfirmasi hapus
    │   └── StatusBadge.vue     # Badge status buku
    ├── layouts/
    │   └── AppLayout.vue       # Navbar, area konten, logout
    ├── router/
    │   └── index.js            # Definisi route + navigation guard
    ├── services/
    │   ├── api.js              # Instance Axios + interceptors
    │   └── bookService.js      # Fungsi API buku (GET/POST/PUT/DELETE)
    ├── stores/
    │   ├── auth.js             # Store Pinia: login/logout/token/user
    │   └── index.js            # Instance Pinia (dipakai di router & main)
    ├── utils/
    │   ├── apiError.js         # Ekstraksi pesan & error validasi
    │   ├── books.js            # Normalisasi data & status buku
    │   └── tokenStorage.js     # Helper localStorage token/user
    └── views/
        ├── DashboardView.vue   # Halaman utama CRUD buku
        ├── LoginView.vue       # Halaman login
        └── NotFoundView.vue    # Halaman 404
```

---

## 4. Persyaratan

- **Node.js 18+** (disarankan 20/22)
- npm 9+
- REST API Laravel untuk Sistem Perpustakaan yang berjalan (default `http://localhost:8000`)

---

## 5. Instalasi & Menjalankan

### 5.1 Siapkan environment

```bash
cd frontend
```

Salin file environment:

**Windows (PowerShell / CMD):**

```powershell
copy .env.example .env
```

**macOS / Linux:**

```bash
cp .env.example .env
```

Isi `.env`:

```dotenv
VITE_API_BASE_URL=http://localhost:8000/api
```

> `VITE_API_BASE_URL` **wajib** berakhiran `/api` dan tanpa trailing slash. Setelah mengubah `.env`, restart dev server.

### 5.2 Install dependency

```bash
npm install
```

### 5.3 Jalankan API Laravel

Dari folder backend (contoh):

```bash
php artisan migrate --seed
php artisan serve
```

API tersedia di `http://localhost:8000/api`.

Jika memakai backend yang ada di folder `../backend`, akun demo hasil seeder adalah:

```text
Email    : demo@library.test
Password : password
```

### 5.4 Jalankan frontend

```bash
npm run dev
```

Buka **http://localhost:5173**. Route `/` akan mengarah ke `/dashboard`; jika belum login, Anda dialihkan ke `/login`.

### 5.5 Build produksi

```bash
npm run build
npm run preview   # pratinjau hasil build
```

Hasil build ada di `dist/`. Karena router memakai **history mode**, web server produksi harus mengarahkan semua request yang tidak menemukan file ke `index.html` (SPA fallback).

---

## 6. Konfigurasi Environment

`.env.example`:

```dotenv
VITE_API_BASE_URL=http://localhost:8000/api
```

Dipakai **hanya** di `src/services/api.js`:

```js
const baseURL = (import.meta.env.VITE_API_BASE_URL || '').replace(/\/+$/, '')

const api = axios.create({
  baseURL,
  timeout: 20000,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})
```

Tidak ada URL API yang di-hardcode di komponen. Semua pemanggilan memakai path relatif, contoh:

```js
api.get('/books')
api.post('/books', payload)
api.put(`/books/${id}`, payload)
api.delete(`/books/${id}`)
```

---

## 7. Kontrak API yang Digunakan

Semua endpoint relatif terhadap `VITE_API_BASE_URL`.

| Method | Endpoint | Auth | Body / Keterangan |
| --- | --- | --- | --- |
| `POST` | `/login` | — | `{ email, password }` → mengembalikan token |
| `POST` | `/logout` | Bearer | Mencabut token aktif |
| `GET` | `/me` | Bearer | Profil user yang sedang login (dipakai bila respons login hanya berisi token) |
| `GET` | `/books` | Bearer | Daftar buku |
| `POST` | `/books` | Bearer | `{ title, author, isbn, category, status }` — `isbn` & `category` opsional |
| `PUT` | `/books/{id}` | Bearer | `{ title, author, isbn, category, status }` |
| `DELETE` | `/books/{id}` | Bearer | Menghapus buku |

### 7.1 Format respons login yang didukung

```json
{
  "token": "1|xxxxxxxx",
  "user": { "id": 1, "name": "Admin", "email": "admin@example.com" }
}
```

Juga mendukung `{ "data": { "token": "...", "user": {...} } }`, `access_token`, atau hanya token tanpa objek `user` (profil lalu diambil dari `GET /me`).

Format di atas sama dengan respons backend Laravel pada folder `backend/`:

```json
{
  "message": "Login berhasil.",
  "data": {
    "user": { "id": 1, "name": "Demo Librarian", "email": "demo@library.test" },
    "token": "1|xxxxxxxx",
    "token_type": "Bearer"
  }
}
```

### 7.2 Format respons daftar buku yang didukung

```json
[{ "id": 1, "title": "...", "author": "...", "isbn": "...", "category": "...", "status": "available" }]
```

```json
{ "data": [ ... ] }
```

```json
{
  "data": [ ... ],
  "current_page": 1,
  "last_page": 1,
  "total": 6
}
```

Jika respons berupa paginator, frontend mengambil seluruh halaman (maksimal 25 halaman) agar pencarian dan statistik mencakup semua buku.

### 7.3 Nilai status

| Nilai dikirim | Label UI | Makna |
| --- | --- | --- |
| `available` | Tersedia | Buku siap dipinjam |
| `borrowed` | Dipinjam | Buku sedang dipinjam |

Frontend juga membaca variasi umum seperti `Tersedia`, `tersedia`, `dipinjam`, `out`, dan `in_stock`.

---

## 8. Alur Autentikasi & Proteksi Route

1. **Login** (`LoginView.vue`)
   - Validasi email/password di client.
   - `auth.login()` mengirim `POST /login`.
   - Token disimpan ke Pinia (`auth.token`) dan localStorage (`perpustakaan.token`).
   - Pengguna diarahkan ke `redirect` yang diminta guard, atau `/dashboard`.

2. **Request berikutnya** (`services/api.js`)
   - Request interceptor membaca token dan menambahkan header:
     ```http
     Authorization: Bearer <token>
     ```
   - Response interceptor menangani `401` (selain request login): hapus token, reset store, redirect ke `/login?redirect=...&reason=session-expired`.

3. **Navigation guard** (`router/index.js`)
   ```js
   router.beforeEach((to) => {
     const auth = useAuthStore(pinia)

     if (to.matched.some((r) => r.meta.requiresAuth) && !auth.isAuthenticated) {
       return { name: 'login', query: { redirect: to.fullPath } }
     }

     if (to.meta.guestOnly && auth.isAuthenticated) {
       return { name: 'dashboard' }
     }

     return true
   })
   ```
   - `/dashboard` dan `/books` memiliki `meta.requiresAuth`.
   - `/login` memiliki `meta.guestOnly`.

4. **Logout** (`AppLayout.vue` → `auth.logout()`)
   - Memanggil `POST /logout` (kegagalan server tetap melanjutkan logout lokal).
   - Menghapus token & user dari Pinia + localStorage.
   - Redirect ke `/login?loggedOut=1`.

---

## 9. Catatan Pengujian Manual

1. Buka `/dashboard` tanpa login → dialihkan ke `/login?redirect=/dashboard`.
2. Login dengan akun valid → masuk dashboard dan token tersimpan.
3. Tambah buku → validasi wajib isi tampil; setelah valid, buku muncul di tabel.
4. Edit buku dan ubah status → badge status berubah. Tombol edit/hapus hanya aktif untuk buku milik user yang login; backend menolak user lain dengan `403`.
5. Cari buku atau filter status → daftar terfilter; tombol reset mengembalikan semua.
6. Hapus buku → dialog konfirmasi; setelah dikonfirmasi data hilang.
7. Logout → kembali ke login, token localStorage kosong, halaman CRUD tidak bisa diakses lagi.
8. Set token palsu di localStorage lalu buka `/dashboard` → API `401` → token dibersihkan dan kembali ke login.
9. Buka lebar layar < 820px → tabel berubah menjadi kartu.

---

## 10. Troubleshooting

| Masalah | Penyebab & solusi |
| --- | --- |
| **CORS error** | Izinkan origin frontend (mis. `http://localhost:5173`) di `config/cors.php` Laravel, termasuk header `Authorization`. Jalankan `php artisan config:clear`. |
| **401 setelah login** | Pastikan respons `/login` memuat field `token`/`access_token`. Login memakai Bearer token, bukan cookie Sanctum. |
| **Network Error** | Cek `VITE_API_BASE_URL`, pastikan `php artisan serve` berjalan, dan restart Vite setelah mengubah `.env`. |
| **Token tidak terkirim** | Pastikan login berhasil dan localStorage berisi `perpustakaan.token`. Interceptor hanya menambahkan header jika token ada. |
| **Data tidak muncul** | Periksa format respons `/books`. Frontend mendukung array langsung, `{ data: [...] }`, dan paginator Laravel. |
| **Validasi ISBN ditolak** | ISBN bersifat opsional dan maksimal 20 karakter. Backend juga menolak ISBN duplikat; pesan validasinya otomatis tampil pada field ISBN. |
| **Refresh di route selain `/` 404** | Konfigurasikan SPA fallback web server ke `index.html` (history mode). |
