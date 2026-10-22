<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Book>
     */
    protected $model = Book::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'author' => fake()->name(),
            'isbn' => fake()->unique()->isbn13(),
            'category' => fake()->randomElement(['Fiksi', 'Sains', 'Sejarah', 'Teknologi', 'Biografi']),
            'status' => Book::STATUS_AVAILABLE,
        ];
    }

    /**
     * Indicate that the book is currently borrowed.
     */
    public function borrowed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Book::STATUS_BORROWED,
        ]);
    }
}
