<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;

class BookControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_all_books()
    {
        $books = Book::factory()->count(5)->create();

        $response = $this->getJson('/books');

        $response->assertStatus(200)
            ->assertJsonCount(5)
            ->assertJsonStructure([
                '*' => ['id', 'title', 'description', 'publish_date', 'author_id']
            ]);
    }

    public function test_store_creates_new_book()
    {
        $author = Author::factory()->create();
        $bookData = [
            'title' => 'Buku Baru',
            'description' => 'Ini adalah deskripsi buku baru',
            'publish_date' => '2023-01-01',
            'author_id' => $author->id
        ];

        $response = $this->postJson('/books', $bookData);

        $response->assertStatus(201)
            ->assertJsonFragment($bookData);

        $this->assertDatabaseHas('books', $bookData);
    }

    public function test_store_fails_with_invalid_data()
    {
        $response = $this->postJson('/books', []);

        $response->assertStatus(422);
    }

    public function test_show_returns_book()
    {
        $book = Book::factory()->create();

        $response = $this->getJson("/books/{$book->id}");

        $response->assertStatus(200)
            ->assertJson($book->toArray());
    }

    public function test_show_returns_404_for_non_existent_book()
    {
        $response = $this->getJson('/books/999');

        $response->assertStatus(404);
    }

    public function test_update_modifies_book()
    {
        $book = Book::factory()->create();
        $newData = [
            'title' => 'Judul Diperbarui',
            'description' => 'Deskripsi diperbarui',
            'publish_date' => '2023-02-02',
            'author_id' => $book->author_id
        ];

        $response = $this->putJson("/books/{$book->id}", $newData);

        $response->assertStatus(200)
            ->assertJsonFragment($newData);

        $this->assertDatabaseHas('books', $newData);
    }

    public function test_update_fails_with_invalid_data()
    {
        $book = Book::factory()->create();

        $response = $this->putJson("/books/{$book->id}", []);

        $response->assertStatus(422);
    }

    public function test_destroy_deletes_book()
    {
        $book = Book::factory()->create();

        $response = $this->deleteJson("/books/{$book->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_books_by_author_returns_correct_books()
    {
        $author = Author::factory()->create();
        $books = Book::factory()->count(3)->create(['author_id' => $author->id]);
        Book::factory()->count(2)->create(); // buku oleh penulis lain

        $response = $this->getJson("/authors/{$author->id}/books");

        $response->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonFragment(['id' => $books[0]->id])
            ->assertJsonFragment(['id' => $books[1]->id])
            ->assertJsonFragment(['id' => $books[2]->id]);
    }

    public function test_books_by_author_returns_404_for_non_existent_author()
    {
        $response = $this->getJson('/authors/999/books');

        $response->assertStatus(404);
    }

    public function test_index_uses_cache()
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn(Book::factory()->count(5)->create());

        $response = $this->getJson('/books');

        $response->assertStatus(200)
            ->assertJsonCount(5);
    }

    public function test_store_clears_cache()
    {
        $author = Author::factory()->create();

        Cache::shouldReceive('forget')->once()->with('books.all');
        Cache::shouldReceive('forget')->once()->with("books.author.{$author->id}");

        $bookData = [
            'title' => 'Buku Baru',
            'description' => 'Deskripsi buku baru',
            'publish_date' => '2023-01-01',
            'author_id' => $author->id
        ];

        $response = $this->postJson('/books', $bookData);

        $response->assertStatus(201);
    }

    public function test_update_clears_cache()
    {
        $book = Book::factory()->create();

        Cache::shouldReceive('forget')->once()->with('books.all');
        Cache::shouldReceive('forget')->once()->with("books.author.{$book->author_id}");
        Cache::shouldReceive('forget')->once()->with("books.author.{$book->author_id}");
        Cache::shouldReceive('forget')->once()->with("book.{$book->id}");

        $newData = [
            'title' => 'Judul Diperbarui',
            'description' => 'Deskripsi diperbarui',
            'publish_date' => '2023-02-02',
            'author_id' => $book->author_id
        ];

        $response = $this->putJson("/books/{$book->id}", $newData);

        $response->assertStatus(200);
    }

    public function test_destroy_clears_cache()
    {
        $book = Book::factory()->create();

        Cache::shouldReceive('forget')->once()->with('books.all');
        Cache::shouldReceive('forget')->once()->with("books.author.{$book->author_id}");
        Cache::shouldReceive('forget')->once()->with("book.{$book->id}");

        $response = $this->deleteJson("/books/{$book->id}");

        $response->assertStatus(204);
    }
}
