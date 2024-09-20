<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;

class AuthorControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_all_authors()
    {
        $authors = Author::factory()->count(5)->create();

        $response = $this->getJson('/authors');

        $response->assertStatus(200)
            ->assertJsonCount(5)
            ->assertJsonStructure([
                '*' => ['id', 'name', 'bio', 'birth_date']
            ]);
    }

    public function test_store_creates_new_author()
    {
        $authorData = [
            'name' => 'Penulis Baru',
            'bio' => 'Ini adalah biografi penulis baru',
            'birth_date' => '1990-01-01'
        ];

        $response = $this->postJson('/authors', $authorData);

        $response->assertStatus(201)
            ->assertJsonFragment($authorData);

        $this->assertDatabaseHas('authors', $authorData);
    }

    public function test_store_fails_with_invalid_data()
    {
        $response = $this->postJson('/authors', []);

        $response->assertStatus(422);
    }

    public function test_show_returns_author()
    {
        $author = Author::factory()->create();

        $response = $this->getJson("/authors/{$author->id}");

        $response->assertStatus(200)
            ->assertJson($author->toArray());
    }

    public function test_show_returns_404_for_non_existent_author()
    {
        $response = $this->getJson('/authors/999');

        $response->assertStatus(404);
    }

    public function test_update_modifies_author()
    {
        $author = Author::factory()->create();
        $newData = [
            'name' => 'Nama Penulis Diperbarui',
            'bio' => 'Biografi penulis yang diperbarui',
            'birth_date' => '1985-05-05'
        ];

        $response = $this->putJson("/authors/{$author->id}", $newData);

        $response->assertStatus(200)
            ->assertJsonFragment($newData);

        $this->assertDatabaseHas('authors', $newData);
    }

    public function test_update_fails_with_invalid_data()
    {
        $author = Author::factory()->create();

        $response = $this->putJson("/authors/{$author->id}", []);

        $response->assertStatus(422);
    }

    public function test_destroy_deletes_author()
    {
        $author = Author::factory()->create();

        $response = $this->deleteJson("/authors/{$author->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('authors', ['id' => $author->id]);
    }

    public function test_destroy_returns_500_for_non_existent_author()
    {
        $response = $this->deleteJson('/authors/999');

        $response->assertStatus(500);
    }

    public function test_index_uses_cache()
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn(Author::factory()->count(3)->create());

        $response = $this->getJson('/authors');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_show_uses_cache()
    {
        $author = Author::factory()->create();

        Cache::shouldReceive('remember')
            ->once()
            ->andReturn($author);

        $response = $this->getJson("/authors/{$author->id}");

        $response->assertStatus(200)
            ->assertJson($author->toArray());
    }

    public function test_store_clears_cache()
    {
        Cache::shouldReceive('forget')->once()->with('authors.all');

        $authorData = [
            'name' => 'Penulis Baru',
            'bio' => 'Biografi baru',
            'birth_date' => '1990-01-01'
        ];

        $response = $this->postJson('/authors', $authorData);

        $response->assertStatus(201);
    }

    public function test_update_clears_cache()
    {
        $author = Author::factory()->create();

        Cache::shouldReceive('forget')->once()->with('authors.all');
        Cache::shouldReceive('forget')->once()->with("author.{$author->id}");

        $newData = [
            'name' => 'Nama Diperbarui',
            'bio' => 'Bio diperbarui',
            'birth_date' => '1985-05-05'
        ];

        $response = $this->putJson("/authors/{$author->id}", $newData);

        $response->assertStatus(200);
    }

    public function test_destroy_clears_cache()
    {
        $author = Author::factory()->create();

        Cache::shouldReceive('forget')->once()->with('authors.all');
        Cache::shouldReceive('forget')->once()->with("author.{$author->id}");

        $response = $this->deleteJson("/authors/{$author->id}");

        $response->assertStatus(204);
    }
}
