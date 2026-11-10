<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; 
use Exception;

class BookController extends Controller
{
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
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->where('category', $request->string('category')->toString());
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status')->toString());
            })
            ->latest('id')
            ->get();

        return response()->json([
            'message' => 'Daftar buku berhasil diambil.',
            'data' => $books,
            'meta' => [
                'total' => $books->count(),
            ],
        ]);
    }

    public function store(StoreBookRequest $request): JsonResponse
    {
        try {
            $book = $request->user()->books()->create($request->validated());

            Log::info('Pengguna berhasil menyimpan data buku baru', [
                'user_id' => $request->user()->id,
                'action' => 'create_book',
                'book_id' => $book->id,
                'reason' => 'Data input valid dan berhasil dimasukkan ke tabel basis data'
            ]);

            return response()->json([
                'message' => 'Buku berhasil ditambahkan.',
                'data' => $book->load('user:id,name,email'),
            ], 201);

        } catch (Exception $e) {
            Log::error('Sistem gagal menghubungi basis data saat menyimpan buku', [
                'user_id' => $request->user()->id,
                'action' => 'create_book_db_error',
                'reason' => 'Terjadi gangguan koneksi atau kegagalan query database: ' . $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan pada server database.',
            ], 503);
        }
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

        Log::info('Pengguna berhasil memperbarui data buku', [
            'user_id' => $request->user()->id,
            'action' => 'update_book',
            'book_id' => $book->id,
            'reason' => 'Pengguna adalah pemilik sah dan data pembaruan valid'
        ]);

        return response()->json([
            'message' => 'Buku berhasil diperbarui.',
            'data' => $book->load('user:id,name,email'),
        ]);
    }

    public function destroy(Request $request, Book $book): JsonResponse
    {
        $this->ensureUserOwnsBook($request, $book);

        $bookId = $book->id;
        $book->delete();

        Log::info('Pengguna berhasil menghapus data buku', [
            'user_id' => $request->user()->id,
            'action' => 'delete_book',
            'book_id' => $bookId,
            'reason' => 'Pengguna adalah pemilik sah dan permintaan hapus dieksekusi'
        ]);

        return response()->json([
            'message' => 'Buku berhasil dihapus.',
            'data' => [
                'id' => $bookId,
            ],
        ]);
    }

    private function ensureUserOwnsBook(Request $request, Book $book): void
    {
        if ($book->user_id !== $request->user()->id) {
            Log::warning('Peringatan keamanan: pengguna mencoba memodifikasi buku milik user lain', [
                'user_id' => $request->user()->id,
                'owner_id' => $book->user_id,
                'book_id' => $book->id,
                'action' => 'unauthorized_book_modification',
                'reason' => 'ID pengguna yang login tidak cocok dengan pemilik terdaftar buku ini'
            ]);

            abort(403, 'Anda tidak berhak mengubah atau menghapus buku milik user lain.');
        }
    }
}
