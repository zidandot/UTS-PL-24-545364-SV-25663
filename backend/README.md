# 📚 Library Management API — Laravel 11 + Sanctum

RESTful API untuk tema **Sistem Perpustakaan / Peminjaman Buku**. Dibuat dengan **Laravel 11**, autentikasi **Bearer Token (Laravel Sanctum)**, database **SQLite** (default, mudah dijalankan), **PHPUnit Feature Test**, dan **Postman Collection** siap pakai.

| Item | Keterangan |
| --- | --- |
| Framework | Laravel 11.57 |
| Bahasa | PHP 8.2+ |
| Autentikasi | Laravel Sanctum (personal access token) |
| Database | SQLite (default), bisa diganti MySQL |
| Testing | PHPUnit 10 — **20 tests, 91 assertions, semua GREEN** |
| Postman | `postman_collection.json` (10 request, 20 assertion, semua lulus) |

---

## 1. Fitur

- Login dan logout berbasis **Bearer Token** dengan Sanctum.
- Semua endpoint CRUD Book diproteksi middleware `auth:sanctum`.
- CRUD lengkap: list, detail, create, update (termasuk ubah status), delete.
- Validasi request dengan status **422 Unprocessable Content** dan pesan Bahasa Indonesia.
- **401 Unauthorized** dalam format JSON saat token tidak ada / tidak valid / sudah dicabut.
- **201 Created** saat buku berhasil dibuat.
- Hanya pemilik buku (`user_id`) yang boleh mengubah / menghapus buku (**403 Forbidden**).
- Filter daftar buku: `?search=`, `?category=`, `?status=available|borrowed`.
- Semua respons (termasuk error) **JSON murni** — tidak ada redirect/HTML/stack trace.
- Seeder akun demo + 5 buku contoh.
- Feature test otomatis dan Postman Collection dengan penyimpanan token otomatis.

---

## 2. Prasyarat

- PHP **8.2** atau lebih baru (ekstensi `pdo_sqlite`, `mbstring`, `openssl`, `curl` aktif)
- **Composer 2**
- **Postman** (untuk mencoba collection)
- Git Bash / Terminal (contoh `curl` di bawah memakai Bash; di Windows bisa pakai Postman)

Cek versi:

```bash
php -v
composer -V
```

---

## 3. Instalasi Langkah demi Langkah

### Langkah 1 — Ambil dependency

```bash
composer install
```

### Langkah 2 — Siapkan file environment

Windows (PowerShell / CMD):

```powershell
copy .env.example .env
php artisan key:generate
```

Linux / macOS / Git Bash:

```bash
cp .env.example .env
php artisan key:generate
```

### Langkah 3 — Konfigurasi database

Project ini sudah disetel memakai **SQLite**:

```dotenv
DB_CONNECTION=sqlite
```

Pastikan file database ada:

```powershell
# Windows PowerShell
if (-not (Test-Path database/database.sqlite)) { New-Item database/database.sqlite }
```

```bash
# Linux / macOS / Git Bash
touch database/database.sqlite
```

> Ingin memakai MySQL? Ubah `.env` menjadi:
>
> ```dotenv
> DB_CONNECTION=mysql
> DB_HOST=127.0.0.1
> DB_PORT=3306
> DB_DATABASE=library_api
> DB_USERNAME=root
> DB_PASSWORD=
> ```
>
> lalu buat database `library_api` di MySQL dan jalankan migrasi seperti biasa.

### Langkah 4 — Migrasi + data contoh

```bash
php artisan migrate:fresh --seed
```

Perintah ini membuat tabel `users`, `books`, `personal_access_tokens`, dll. serta akun dan buku demo.

### Langkah 5 — Jalankan server

```bash
php artisan serve
```

Base URL API: **`http://127.0.0.1:8000/api`**

### Langkah 6 — Akun demo

| Email | Password |
| --- | --- |
| `demo@library.test` | `password` |

### Langkah 7 — Jalankan test (opsional, untuk verifikasi)

```bash
php artisan test
```

Hasil yang diharapkan:

```text
PASS  Tests\Feature\AuthTest
PASS  Tests\Feature\BookTest
Tests:    20 passed (91 assertions)
```

### Langkah 8 — Import Postman Collection

1. Buka Postman → **Import** → pilih `postman_collection.json`.
2. Pastikan collection variable `base_url` = `http://127.0.0.1:8000/api`.
3. Jalankan folder **1. Authentication → Login**. Token otomatis disimpan ke variable `token`.
4. Jalankan folder **2. Books (CRUD)**.
5. Folder **3. Negative Scenarios & Logout** berisi contoh 401 dan logout.

---

## 4. Struktur File Penting

```text
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php        # login, logout, me
│   │   │   └── BookController.php       # CRUD buku
│   │   ├── Middleware/
│   │   │   └── ForceJsonResponse.php    # paksa semua respons API = JSON
│   │   └── Requests/
│   │       ├── LoginRequest.php
│   │       ├── StoreBookRequest.php
│   │       └── UpdateBookRequest.php
│   └── Models/
│       ├── Book.php
│       └── User.php                     # memakai HasApiTokens
├── bootstrap/
│   └── app.php                          # registrasi middleware + exception JSON
├── database/
│   ├── factories/
│   │   └── BookFactory.php
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 2026_10_21_100008_create_personal_access_tokens_table.php
│   │   └── 2026_10_21_100100_create_books_table.php
│   ├── seeders/
│   │   └── DatabaseSeeder.php
│   └── database.sqlite
├── routes/
│   └── api.php                          # semua endpoint API
├── tests/
│   └── Feature/
│       ├── AuthTest.php                 # 6 test autentikasi
│       └── BookTest.php                 # 12 test CRUD buku
├── phpunit.xml                          # test memakai SQLite :memory:
├── postman_collection.json
└── README.md
```

---

## 5. Endpoint API

| No | Method | Endpoint | Auth | Keterangan |
| --- | --- | --- | --- | --- |
| 1 | POST | `/api/login` | Publik | Mendapatkan Bearer Token |
| 2 | POST | `/api/logout` | Bearer | Mencabut token aktif |
| 3 | GET | `/api/books` | Bearer | Daftar seluruh buku |
| 4 | GET | `/api/books/{id}` | Bearer | Detail satu buku |
| 5 | POST | `/api/books` | Bearer | Tambah buku (201 Created) |
| 6 | PUT | `/api/books/{id}` | Bearer | Ubah data/status buku |
| 7 | DELETE | `/api/books/{id}` | Bearer | Hapus buku |
| 8 | GET | `/api/me` | Bearer | (bonus) Profil user aktif |

Header wajib untuk endpoint terproteksi:

```http
Accept: application/json
Authorization: Bearer {token}
Content-Type: application/json
```

### Atribut Book

| Field | Tipe | Aturan |
| --- | --- | --- |
| `id` | integer | auto |
| `user_id` | integer | otomatis dari user login |
| `title` | string | **wajib**, maks. 255 |
| `author` | string | **wajib**, maks. 255 |
| `isbn` | string | opsional, unik, maks. 20 |
| `category` | string | opsional, maks. 100 |
| `status` | enum | `available` / `borrowed`, default `available` |
| `created_at`, `updated_at` | timestamp | auto |

---

## 6. Kode Inti

### 6.1 `routes/api.php`

```php
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('books', BookController::class);
});
```

`Route::apiResource('books', ...)` otomatis membuat 5 route: `index`, `store`, `show`, `update` (PUT/PATCH), `destroy`.

### 6.2 `app/Http/Controllers/AuthController.php`

```php
public function login(LoginRequest $request): JsonResponse
{
    $credentials = $request->validated();

    $user = User::where('email', $credentials['email'])->first();

    if (! $user || ! Hash::check($credentials['password'], $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['Email atau password salah.'],
        ]);
    }

    $token = $user->createToken($credentials['device_name'] ?? 'api-client')->plainTextToken;

    return response()->json([
        'message' => 'Login berhasil.',
        'data' => [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ],
    ]);
}

public function logout(Request $request): JsonResponse
{
    $currentToken = $request->user()->currentAccessToken();

    if ($currentToken instanceof PersonalAccessToken) {
        $currentToken->delete();
    }

    return response()->json([
        'message' => 'Logout berhasil. Token telah dicabut.',
    ]);
}
```

### 6.3 `app/Http/Controllers/BookController.php`

```php
public function index(Request $request): JsonResponse
{
    $books = Book::query()
        ->with('user:id,name,email')
        ->when($request->filled('search'), function ($query) use ($request) {
            $search = $request->string('search')->toString();

            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%");
            });
        })
        ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')))
        ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
        ->latest('id')
        ->get();

    return response()->json([
        'message' => 'Daftar buku berhasil diambil.',
        'data' => $books,
        'meta' => ['total' => $books->count()],
    ]);
}

public function store(StoreBookRequest $request): JsonResponse
{
    $book = $request->user()->books()->create($request->validated());

    return response()->json([
        'message' => 'Buku berhasil ditambahkan.',
        'data' => $book->load('user:id,name,email'),
    ], 201);
}

public function show(Book $book): JsonResponse
{
    return response()->json([
        'message' => 'Detail buku berhasil diambil.',
        'data' => $book->load('user:id,name,email'),
    ]);
}

public function update(UpdateBookRequest $request, Book $book): JsonResponse
{
    $this->ensureUserOwnsBook($request, $book);

    $book->update($request->validated());

    return response()->json([
        'message' => 'Buku berhasil diperbarui.',
        'data' => $book->load('user:id,name,email'),
    ]);
}

public function destroy(Request $request, Book $book): JsonResponse
{
    $this->ensureUserOwnsBook($request, $book);

    $book->delete();

    return response()->json([
        'message' => 'Buku berhasil dihapus.',
        'data' => ['id' => $book->id],
    ]);
}

private function ensureUserOwnsBook(Request $request, Book $book): void
{
    abort_if(
        $book->user_id !== $request->user()->id,
        403,
        'Anda tidak berhak mengubah atau menghapus buku milik user lain.'
    );
}
```

### 6.4 Validasi `StoreBookRequest`

```php
public function rules(): array
{
    return [
        'title' => ['required', 'string', 'max:255'],
        'author' => ['required', 'string', 'max:255'],
        'isbn' => ['nullable', 'string', 'max:20', 'unique:books,isbn'],
        'category' => ['nullable', 'string', 'max:100'],
        'status' => ['nullable', Rule::in([Book::STATUS_AVAILABLE, Book::STATUS_BORROWED])],
    ];
}
```

`UpdateBookRequest` memakai aturan yang sama dengan tambahan `sometimes`, sehingga bisa update sebagian (misal hanya `status`). ISBN unik mengabaikan ID buku yang sedang diubah.

### 6.5 Migration `create_books_table`

```php
Schema::create('books', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('title');
    $table->string('author');
    $table->string('isbn', 20)->nullable()->unique();
    $table->string('category', 100)->nullable();
    $table->enum('status', ['available', 'borrowed'])->default('available');
    $table->timestamps();
});
```

### 6.6 JSON murni untuk semua respons

`app/Http/Middleware/ForceJsonResponse.php` memaksa header `Accept: application/json`:

```php
public function handle(Request $request, Closure $next): Response
{
    $request->headers->set('Accept', 'application/json');

    return $next($request);
}
```

Middleware didaftarkan untuk group API di `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->api(prepend: [
        ForceJsonResponse::class,
    ]);
})
```

Selain itu, exception handler di `bootstrap/app.php` memastikan error tetap JSON dan tidak membocorkan stack trace:

```php
$exceptions->render(function (AuthenticationException $e, Request $request) {
    if ($request->is('api/*')) {
        return response()->json([
            'message' => 'Unauthenticated. Token tidak valid atau tidak disertakan.',
        ], 401);
    }
});

$exceptions->render(function (HttpExceptionInterface $e, Request $request) {
    if ($request->is('api/*')) {
        $message = match ($e->getStatusCode()) {
            404 => 'Data atau endpoint yang diminta tidak ditemukan.',
            default => $e->getMessage() ?: 'Terjadi kesalahan pada permintaan.',
        };

        return response()->json(['message' => $message], $e->getStatusCode(), $e->getHeaders());
    }
});
```

---

## 7. Contoh Request & Response

### 7.1 Login — `POST /api/login`

```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"demo@library.test","password":"password","device_name":"postman"}'
```

**200 OK**

```json
{
    "message": "Login berhasil.",
    "data": {
        "user": {
            "id": 1,
            "name": "Demo Librarian",
            "email": "demo@library.test"
        },
        "token": "1|Nk1KwOaRifRUtzR3ztMKGujnpX5CbtnOIEG7CDHp8c2b850c",
        "token_type": "Bearer"
    }
}
```

Password salah → **422 Unprocessable Content**

```json
{
    "message": "Email atau password salah.",
    "errors": {
        "email": ["Email atau password salah."]
    }
}
```

### 7.2 Daftar buku — `GET /api/books`

```bash
curl http://127.0.0.1:8000/api/books \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {token}"
```

**200 OK**

```json
{
    "message": "Daftar buku berhasil diambil.",
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "title": "Laskar Pelangi",
            "author": "Andrea Hirata",
            "isbn": "9789793062792",
            "category": "Fiksi",
            "status": "borrowed",
            "created_at": "2026-10-21T10:02:07.000000Z",
            "updated_at": "2026-10-21T10:02:07.000000Z",
            "user": {
                "id": 1,
                "name": "Demo Librarian",
                "email": "demo@library.test"
            }
        }
    ],
    "meta": {
        "total": 1
    }
}
```

Tanpa token → **401 Unauthorized**

```json
{
    "message": "Unauthenticated. Token tidak valid atau tidak disertakan."
}
```

### 7.3 Tambah buku — `POST /api/books`

```bash
curl -X POST http://127.0.0.1:8000/api/books \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{"title":"Clean Code","author":"Robert C. Martin","isbn":"9780132350884","category":"Teknologi","status":"available"}'
```

**201 Created**

```json
{
    "message": "Buku berhasil ditambahkan.",
    "data": {
        "status": "available",
        "title": "Clean Code",
        "author": "Robert C. Martin",
        "isbn": "9780132350884",
        "category": "Teknologi",
        "user_id": 1,
        "updated_at": "2026-10-21T10:01:45.000000Z",
        "created_at": "2026-10-21T10:01:45.000000Z",
        "id": 6,
        "user": {
            "id": 1,
            "name": "Demo Librarian",
            "email": "demo@library.test"
        }
    }
}
```

Validasi gagal (title & author kosong) → **422 Unprocessable Content**

```json
{
    "message": "Judul buku wajib diisi. (and 1 more error)",
    "errors": {
        "title": ["Judul buku wajib diisi."],
        "author": ["Nama penulis wajib diisi."]
    }
}
```

### 7.4 Detail buku — `GET /api/books/{id}`

```bash
curl http://127.0.0.1:8000/api/books/1 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {token}"
```

**200 OK** — format sama seperti item pada daftar buku.
ID tidak ada → **404 Not Found**:

```json
{"message": "Data atau endpoint yang diminta tidak ditemukan."}
```

### 7.5 Ubah status buku — `PUT /api/books/{id}`

```bash
curl -X PUT http://127.0.0.1:8000/api/books/6 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{"status":"borrowed"}'
```

**200 OK**

```json
{
    "message": "Buku berhasil diperbarui.",
    "data": {
        "id": 6,
        "user_id": 1,
        "title": "Clean Code",
        "author": "Robert C. Martin",
        "isbn": "9780132350884",
        "category": "Teknologi",
        "status": "borrowed",
        "created_at": "2026-10-21T10:01:45.000000Z",
        "updated_at": "2026-10-21T10:01:46.000000Z"
    }
}
```

Buku milik user lain → **403 Forbidden**

```json
{"message": "Anda tidak berhak mengubah atau menghapus buku milik user lain."}
```

### 7.6 Hapus buku — `DELETE /api/books/{id}`

```bash
curl -X DELETE http://127.0.0.1:8000/api/books/6 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {token}"
```

**200 OK**

```json
{
    "message": "Buku berhasil dihapus.",
    "data": {"id": 6}
}
```

### 7.7 Logout — `POST /api/logout`

```bash
curl -X POST http://127.0.0.1:8000/api/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {token}"
```

**200 OK**

```json
{"message": "Logout berhasil. Token telah dicabut."}
```

Token yang sudah dicabut dipakai lagi → **401 Unauthorized**.

---

## 8. Feature Test

Perintah:

```bash
php artisan test
```

Konfigurasi `phpunit.xml` memakai database SQLite in-memory agar test tidak mengubah database development:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

### `tests/Feature/AuthTest.php` (6 test)

1. Login valid menerima Bearer Token dan token bisa dipakai di `/api/me`.
2. Login dengan password salah → 422.
3. Login tanpa email/password → 422.
4. Logout mencabut token; token lama → 401.
5. `GET /api/books` tanpa token → 401.
6. `GET /api/books` dengan token palsu → 401.

### `tests/Feature/BookTest.php` (12 test)

1. Menampilkan seluruh buku + struktur JSON.
2. Filter buku berdasarkan status.
3. Detail satu buku (termasuk verifikasi `user_id` otomatis dari user login).
4. Buku tidak ditemukan → 404.
5. Membuat buku → **201 Created** (+ default status `available`) dan tersimpan di database.
6. Validasi title/author kosong → **422**.
7. Status tidak valid → **422**.
8. Update buku dan status → 200.
9. Update dengan title kosong → **422**.
10. Hapus buku → 200 + terhapus dari database.
11. User lain tidak boleh update/delete → **403**.
12. Semua endpoint buku tanpa token → **401**.

> Ditambah 2 test bawaan Laravel (`tests/Unit/ExampleTest.php` dan `tests/Feature/ExampleTest.php`), total menjadi **20 test**.

Contoh potongan test:

```php
public function test_user_can_create_a_book(): void
{
    $payload = [
        'title' => 'Clean Code',
        'author' => 'Robert C. Martin',
        'isbn' => '9780132350884',
        'category' => 'Teknologi',
        'status' => Book::STATUS_AVAILABLE,
    ];

    $response = $this->postJson('/api/books', $payload);

    $response
        ->assertCreated()
        ->assertJsonPath('data.title', 'Clean Code')
        ->assertJsonPath('data.user_id', $this->user->id);

    $this->assertDatabaseHas('books', [
        'user_id' => $this->user->id,
        'title' => 'Clean Code',
    ]);
}

public function test_create_book_validation_fails_when_title_and_author_are_empty(): void
{
    $this->postJson('/api/books', ['title' => '', 'author' => ''])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title', 'author']);
}
```

Hasil nyata:

```text
PASS  Tests\Feature\AuthTest
✓ user can login with valid credentials and receive bearer token
✓ login fails with wrong password
✓ login requires email and password
✓ user can logout and the token is revoked
✓ books endpoint returns 401 without token
✓ books endpoint returns 401 with invalid token

PASS  Tests\Feature\BookTest
✓ authenticated user can list all books
✓ authenticated user can filter books by status
✓ user can view a single book
✓ viewing unknown book returns 404
✓ user can create a book
✓ create book validation fails when title and author are empty
✓ create book validation fails when status is invalid
✓ user can update a book and change its status
✓ update book validation fails when title is empty
✓ user can delete a book
✓ user cannot update or delete a book owned by someone else
✓ all book endpoints require a valid token

Tests:    20 passed (91 assertions)
```

---

## 9. Postman Collection

File: **`postman_collection.json`** (root project).

### Isi collection

| Folder | Request | Ekspektasi |
| --- | --- | --- |
| 1. Authentication | Login | 200, token disimpan otomatis |
| 1. Authentication | Get Current User (Me) | 200 |
| 2. Books (CRUD) | Get All Books | 200 |
| 2. Books (CRUD) | Create Book | **201**, `book_id` disimpan otomatis |
| 2. Books (CRUD) | Get Book Detail | 200 |
| 2. Books (CRUD) | Update Book (Borrowed) | 200, status `borrowed` |
| 2. Books (CRUD) | Delete Book | 200 |
| 3. Negative Scenarios & Logout | Get Books Without Token | **401** |
| 3. Negative Scenarios & Logout | Get Books With Invalid Token | **401** |
| 3. Negative Scenarios & Logout | Logout | 200, token dihapus dari variable |

### Skema Bearer Token

- Collection-level **Authorization** bertipe `Bearer` dengan value `{{token}}`.
- Request **Login** memakai `noauth` karena belum punya token.
- Test script Login:

```javascript
const body = pm.response.json();
pm.collectionVariables.set('token', body.data.token);
```

- Test script Create Book menyimpan ID buku baru:

```javascript
pm.collectionVariables.set('book_id', pm.response.json().data.id);
```

- Request negatif memakai `noauth` / token palsu untuk membuktikan respons **401**.

### Collection variables

| Variable | Nilai awal | Keterangan |
| --- | --- | --- |
| `base_url` | `http://127.0.0.1:8000/api` | Alamat server |
| `token` | _(kosong)_ | Diisi otomatis setelah Login |
| `book_id` | `1` | Diperbarui otomatis setelah Create Book |
| `book_isbn` | `9780132350884` | Diisi otomatis oleh pre-request script (unik) |

### Menjalankan seluruh collection (opsional, via Newman)

```bash
npx newman run postman_collection.json --env-var base_url=http://127.0.0.1:8000/api
```

Hasil terverifikasi: **10 request, 20 assertion, 0 gagal**.

---

## 10. Alur Pengujian Manual Singkat

1. `POST /api/login` dengan akun demo → salin `data.token`.
2. `GET /api/books` tanpa header Authorization → **401**.
3. `GET /api/books` dengan `Authorization: Bearer {token}` → **200**.
4. `POST /api/books` body `{"title":"","author":""}` → **422**.
5. `POST /api/books` body valid → **201**, catat `id`.
6. `PUT /api/books/{id}` body `{"status":"borrowed"}` → **200**.
7. `DELETE /api/books/{id}` → **200**.
8. `GET /api/books/{id}` setelah dihapus → **404**.
9. `POST /api/logout` → **200**.
10. `GET /api/books` memakai token lama → **401**.

---

## 11. Catatan Desain

- **Token hashing**: Sanctum menyimpan hash token di tabel `personal_access_tokens`; plain token hanya dikembalikan sekali saat login.
- **Logout** hanya mencabut token yang sedang dipakai, bukan semua sesi.
- **Kepemilikan data**: `user_id` diisi otomatis dari user yang login (tidak bisa dikirim lewat request). Semua user terautentikasi boleh melihat katalog, tetapi hanya pemilik yang boleh mengubah/menghapus.
- **Status** dibatasi pada `available` dan `borrowed`; default `available`.
- **ISBN** opsional dan unik; mengirim ISBN yang sudah dipakai → 422.
- **Konsistensi format**: semua respons memakai envelope `message` + `data` (+ `meta` untuk list, + `errors` untuk validasi).

---

## 12. Troubleshooting

| Masalah | Solusi |
| --- | --- |
| `could not find driver` | Aktifkan ekstensi `pdo_sqlite` / `pdo_mysql` di `php.ini`, lalu restart terminal. |
| `Database file at path ... does not exist` | Buat file `database/database.sqlite` (Langkah 3). |
| Selalu 401 padahal sudah login | Kirim `Authorization: Bearer {token}` tanpa tanda kutip; pastikan token belum di-logout. |
| 419 / CSRF | Endpoint API ada di `/api/*` dan tidak memakai CSRF; jangan memanggil route web. |
| `Route [login] not defined` | Pastikan request mengirim `Accept: application/json` (Postman collection sudah mengaturnya). |
| Perubahan `.env` tidak terbaca | `php artisan config:clear` lalu jalankan ulang server. |
| Port 8000 dipakai | `php artisan serve --port=8001` dan ubah `base_url` Postman. |
| Ingin reset data | `php artisan migrate:fresh --seed`. |

---

## 13. Ringkasan Perintah

```bash
composer install
copy .env.example .env        # Windows
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
php artisan test
```

Selamat mencoba! 🚀
