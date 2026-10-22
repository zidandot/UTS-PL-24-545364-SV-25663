<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with a demo account and sample books.
     */
    public function run(): void
    {
        $librarian = User::create([
            'name' => 'Demo Librarian',
            'email' => 'demo@library.test',
            'password' => 'password',
        ]);

        $books = [
            [
                'title' => 'Laskar Pelangi',
                'author' => 'Andrea Hirata',
                'isbn' => '9789793062792',
                'category' => 'Fiksi',
                'status' => Book::STATUS_BORROWED,
            ],
            [
                'title' => 'Bumi Manusia',
                'author' => 'Pramoedya Ananta Toer',
                'isbn' => '9789799731234',
                'category' => 'Fiksi',
                'status' => Book::STATUS_AVAILABLE,
            ],
            [
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'isbn' => '9780132350884',
                'category' => 'Teknologi',
                'status' => Book::STATUS_AVAILABLE,
            ],
            [
                'title' => 'Sapiens: Riwayat Singkat Umat Manusia',
                'author' => 'Yuval Noah Harari',
                'isbn' => '9786024246986',
                'category' => 'Sejarah',
                'status' => Book::STATUS_AVAILABLE,
            ],
            [
                'title' => 'Filosofi Teras',
                'author' => 'Henry Manampiring',
                'isbn' => '9786024125189',
                'category' => 'Pengembangan Diri',
                'status' => Book::STATUS_BORROWED,
            ],
            [
                'title' => 'Bumi',
                'author' => 'Tere Liye',
                'isbn' => '9786020330814',
                'category' => 'Fiksi',
                'status' => Book::STATUS_AVAILABLE,
            ],
            [
                'title' => 'Perahu Kertas',
                'author' => 'Dee Lestari',
                'isbn' => '9786020334898',
                'category' => 'Fiksi',
                'status' => Book::STATUS_AVAILABLE,
            ],
            [
                'title' => 'Hujan',
                'author' => 'Tere Liye',
                'isbn' => '9786020825945',
                'category' => 'Fiksi',
                'status' => Book::STATUS_BORROWED,
            ],
            [
                'title' => 'Refactoring: Improving the Design of Existing Code',
                'author' => 'Martin Fowler',
                'isbn' => '9780134757599',
                'category' => 'Teknologi',
                'status' => Book::STATUS_AVAILABLE,
            ],
            [
                'title' => 'The Pragmatic Programmer',
                'author' => 'Andrew Hunt & David Thomas',
                'isbn' => '9780135957059',
                'category' => 'Teknologi',
                'status' => Book::STATUS_AVAILABLE,
            ],
            [
                'title' => 'Algoritma dan Pemrograman',
                'author' => 'Rinaldi Munir',
                'isbn' => '9786021516648',
                'category' => 'Teknologi',
                'status' => Book::STATUS_BORROWED,
            ],
            [
                'title' => 'Sejarah Indonesia Modern',
                'author' => 'M. C. Ricklefs',
                'isbn' => '9789794616697',
                'category' => 'Sejarah',
                'status' => Book::STATUS_AVAILABLE,
            ],
            [
                'title' => 'Guns, Germs, and Steel',
                'author' => 'Jared Diamond',
                'isbn' => '9789793062900',
                'category' => 'Sejarah',
                'status' => Book::STATUS_AVAILABLE,
            ],
            [
                'title' => 'Atomic Habits',
                'author' => 'James Clear',
                'isbn' => '9786020633175',
                'category' => 'Pengembangan Diri',
                'status' => Book::STATUS_BORROWED,
            ],
            [
                'title' => 'Berpikir Cepat dan Lambat',
                'author' => 'Daniel Kahneman',
                'isbn' => '9786024246948',
                'category' => 'Pengembangan Diri',
                'status' => Book::STATUS_AVAILABLE,
            ],
        ];

        $librarian->books()->createMany($books);
    }
}
