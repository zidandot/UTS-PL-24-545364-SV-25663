<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_authenticated_user_can_list_all_books(): void
    {
        Book::factory()->count(3)->for($this->user)->create();

        $this->getJson('/api/books')
            ->assertOk()
            ->assertJsonPath('message', 'Daftar buku berhasil diambil.')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'message',
                'data' => [
                    ['id', 'user_id', 'title', 'author', 'isbn', 'category', 'status', 'created_at', 'updated_at'],
                ],
                'meta' => ['total'],
            ]);
    }

    public function test_authenticated_user_can_filter_books_by_status(): void
    {
        Book::factory()->count(2)->for($this->user)->create();
        Book::factory()->borrowed()->for($this->user)->create();

        $this->getJson('/api/books?status=borrowed')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', Book::STATUS_BORROWED);
    }

    public function test_user_can_view_a_single_book(): void
    {
        $book = Book::factory()->for($this->user)->create([
            'title' => 'Bumi Manusia',
            'author' => 'Pramoedya Ananta Toer',
        ]);

        $this->getJson("/api/books/{$book->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $book->id)
            ->assertJsonPath('data.title', 'Bumi Manusia')
            ->assertJsonPath('data.user.id', $this->user->id);
    }

    public function test_viewing_unknown_book_returns_404(): void
    {
        $this->getJson('/api/books/999999')->assertNotFound();
    }

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
            ->assertJsonPath('message', 'Buku berhasil ditambahkan.')
            ->assertJsonPath('data.title', 'Clean Code')
            ->assertJsonPath('data.user_id', $this->user->id)
            ->assertJsonPath('data.status', Book::STATUS_AVAILABLE);

        $this->assertDatabaseHas('books', [
            'user_id' => $this->user->id,
            'title' => 'Clean Code',
            'author' => 'Robert C. Martin',
            'isbn' => '9780132350884',
            'category' => 'Teknologi',
            'status' => Book::STATUS_AVAILABLE,
        ]);
    }

    public function test_create_book_validation_fails_when_title_and_author_are_empty(): void
    {
        $this->postJson('/api/books', [
            'title' => '',
            'author' => '',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'author']);
    }

    public function test_create_book_validation_fails_when_status_is_invalid(): void
    {
        $this->postJson('/api/books', [
            'title' => 'Buku Contoh',
            'author' => 'Penulis Contoh',
            'status' => 'hilang',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_user_can_update_a_book_and_change_its_status(): void
    {
        $book = Book::factory()->for($this->user)->create([
            'status' => Book::STATUS_AVAILABLE,
        ]);

        $this->putJson("/api/books/{$book->id}", [
            'title' => 'Judul Diperbarui',
            'status' => Book::STATUS_BORROWED,
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Buku berhasil diperbarui.')
            ->assertJsonPath('data.title', 'Judul Diperbarui')
            ->assertJsonPath('data.status', Book::STATUS_BORROWED);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Judul Diperbarui',
            'status' => Book::STATUS_BORROWED,
        ]);
    }

    public function test_update_book_validation_fails_when_title_is_empty(): void
    {
        $book = Book::factory()->for($this->user)->create();

        $this->putJson("/api/books/{$book->id}", [
            'title' => '',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');
    }

    public function test_user_can_delete_a_book(): void
    {
        $book = Book::factory()->for($this->user)->create();

        $this->deleteJson("/api/books/{$book->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Buku berhasil dihapus.')
            ->assertJsonPath('data.id', $book->id);

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_user_cannot_update_or_delete_a_book_owned_by_someone_else(): void
    {
        $otherBook = Book::factory()->for(User::factory())->create();

        $this->putJson("/api/books/{$otherBook->id}", [
            'status' => Book::STATUS_BORROWED,
        ])->assertForbidden();

        $this->deleteJson("/api/books/{$otherBook->id}")->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $otherBook->id,
            'status' => Book::STATUS_AVAILABLE,
        ]);
    }

    public function test_all_book_endpoints_require_a_valid_token(): void
    {
        $this->app['auth']->forgetGuards();

        $book = Book::factory()->for($this->user)->create();

        $this->getJson('/api/books')->assertUnauthorized();
        $this->getJson("/api/books/{$book->id}")->assertUnauthorized();
        $this->postJson('/api/books', ['title' => 'X', 'author' => 'Y'])->assertUnauthorized();
        $this->putJson("/api/books/{$book->id}", ['status' => 'borrowed'])->assertUnauthorized();
        $this->deleteJson("/api/books/{$book->id}")->assertUnauthorized();
    }
}
