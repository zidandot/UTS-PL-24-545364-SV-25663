<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display a listing of the books.
     *
     * Optional query params: ?search=, ?category=, ?status=available|borrowed
     */
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

    /**
     * Store a newly created book in storage.
     */
    public function store(StoreBookRequest $request): JsonResponse
    {
        $book = $request->user()->books()->create($request->validated());

        return response()->json([
            'message' => 'Buku berhasil ditambahkan.',
            'data' => $book->load('user:id,name,email'),
        ], 201);
    }

    /**
     * Display the specified book.
     */
    public function show(Book $book): JsonResponse
    {
        return response()->json([
            'message' => 'Detail buku berhasil diambil.',
            'data' => $book->load('user:id,name,email'),
        ]);
    }

    /**
     * Update the specified book in storage.
     */
    public function update(UpdateBookRequest $request, Book $book): JsonResponse
    {
        $this->ensureUserOwnsBook($request, $book);

        $book->update($request->validated());

        return response()->json([
            'message' => 'Buku berhasil diperbarui.',
            'data' => $book->load('user:id,name,email'),
        ]);
    }

    /**
     * Remove the specified book from storage.
     */
    public function destroy(Request $request, Book $book): JsonResponse
    {
        $this->ensureUserOwnsBook($request, $book);

        $book->delete();

        return response()->json([
            'message' => 'Buku berhasil dihapus.',
            'data' => [
                'id' => $book->id,
            ],
        ]);
    }

    /**
     * Only the user who registered a book may modify or delete it.
     */
    private function ensureUserOwnsBook(Request $request, Book $book): void
    {
        abort_if(
            $book->user_id !== $request->user()->id,
            403,
            'Anda tidak berhak mengubah atau menghapus buku milik user lain.'
        );
    }
}
