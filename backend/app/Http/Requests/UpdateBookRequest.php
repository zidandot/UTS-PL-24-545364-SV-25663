<?php

namespace App\Http\Requests;

use App\Models\Book;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Book $book */
        $book = $this->route('book');

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'author' => ['sometimes', 'required', 'string', 'max:255'],
            'isbn' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
                Rule::unique('books', 'isbn')->ignore($book?->id),
            ],
            'category' => ['sometimes', 'nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'required', Rule::in([Book::STATUS_AVAILABLE, Book::STATUS_BORROWED])],
        ];
    }

    /**
     * Custom validation messages (Bahasa Indonesia).
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul buku tidak boleh kosong.',
            'title.max' => 'Judul buku maksimal 255 karakter.',
            'author.required' => 'Nama penulis tidak boleh kosong.',
            'author.max' => 'Nama penulis maksimal 255 karakter.',
            'isbn.unique' => 'ISBN sudah terdaftar pada buku lain.',
            'isbn.max' => 'ISBN maksimal 20 karakter.',
            'category.max' => 'Kategori maksimal 100 karakter.',
            'status.required' => 'Status buku wajib diisi.',
            'status.in' => 'Status harus bernilai "available" atau "borrowed".',
        ];
    }
}
