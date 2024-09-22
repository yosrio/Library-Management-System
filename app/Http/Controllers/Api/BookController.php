<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

/**
 * BookController Class
 *
 * This class handles CRUD operations for the Book model.
 * It provides methods to list, store, update, and delete book data.
 */
class BookController extends Controller
{
    /**
     * Get all book data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $books = Cache::remember(
            'books.all',
            3600,
            fn() => Book::select(['id', 'title', 'description', 'publish_date', 'author_id'])->get()
        );
        return response()->json($books, 200);
    }

    /**
     * Add book data
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'publish_date' => 'required|date',
                'author_id' => 'required|exists:authors,id',
            ]);

            $book = Book::create($validatedData);

            $this->clearBookCache($book->author_id);
            return response()->json($book->makeHidden(['created_at', 'updated_at']), 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while saving the book data'], 500);
        }
    }

    /**
     * Show specific book by id
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $book = Cache::remember("book.{$id}", 3600, fn() => Book::findOrFail($id)->makeHidden(['created_at', 'updated_at']));
            return response()->json($book, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Book not found'], 404);
        }
    }

    /**
     * Update book data
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $book = Book::findOrFail($id);

            $oldAuthorId = $book->author_id;

            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'publish_date' => 'required|date',
                'author_id' => 'required|exists:authors,id',
            ]);

            $book->update($validatedData);

            $this->clearBookCache($oldAuthorId, $book->author_id, $id);
            return response()->json($book->makeHidden(['created_at', 'updated_at']), 200);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Book not found'], 404);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'An error occurred while updating the book data'], 500);
        }
    }

    /**
     * Delete book data
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $book = Book::findOrFail($id);
            $authorId = $book->author_id;
            $book->delete();

            $this->clearBookCache($authorId, null, $id);
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Book not found'], 404);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'An error occurred while deleting the book data'], 500);
        }
    }

    /**
     * Get all books by specific author
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function booksByAuthor($id)
    {
        try {
            $author = Author::findOrFail($id);
            $books = Cache::remember(
                "books.author.{$id}",
                3600,
                fn() => $author->books()->get(['id', 'title', 'description', 'publish_date'])
            );
            return response()->json($books, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Author not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching books by author'], 500);
        }
    }

    /**
     * Clear book-related cache
     *
     * @param int $oldAuthorId
     * @param int|null $newAuthorId
     * @param int|null $bookId
     * @return void
     */
    private function clearBookCache($oldAuthorId, $newAuthorId = null, $bookId = null)
    {
        Cache::forget('books.all');
        Cache::forget("books.author.{$oldAuthorId}");
        if ($newAuthorId) Cache::forget("books.author.{$newAuthorId}");
        if ($bookId) Cache::forget("book.{$bookId}");
    }
}
