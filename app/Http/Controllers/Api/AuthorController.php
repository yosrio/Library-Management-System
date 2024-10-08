<?php

namespace App\Http\Controllers\Api;

use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * AuthorController Class
 *
 * This class handles CRUD operations for the Author model.
 * It provides methods to list, store, update, and delete author data.
 */
class AuthorController extends \App\Http\Controllers\Controller
{
    /**
     * Get all author data
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return response()->json(Cache::remember(
            'authors.all',
            3600,
            fn() => Author::select(['id', 'name', 'bio', 'birth_date'])->get()
        ), 200);
    }

    /**
     * Add author data
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'bio' => 'nullable|string',
                'birth_date' => 'required|date',
            ]);

            $author = Author::create($validatedData);

            $this->clearAuthorCache();
            return response()->json($author->makeHidden(['created_at', 'updated_at']), 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'An error occurred while saving the author data'], 500);
        }
    }

    /**
     * Show specific author by id
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $author = Cache::remember(
            "author.{$id}",
            3600,
            fn() => Author::select(['id', 'name', 'bio', 'birth_date'])->find($id)
        );

        return $author 
            ? response()->json($author, 200)
            : response()->json(['error' => 'Author not found'], 404);
    }

    /**
     * Update author data
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $author = Author::findOrFail($id);

            $author->update($request->validate([
                'name' => 'required|string|max:255',
                'bio' => 'nullable|string',
                'birth_date' => 'required|date',
            ]));

            $this->clearAuthorCache($id);
            return response()->json($author->makeHidden(['created_at', 'updated_at']), 200);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Author not found'], 404);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'An error occurred while updating the author data'], 500);
        }
    }

    /**
     * Delete author data
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $author = Author::findOrFail($id);
            $author->delete();

            $this->clearAuthorCache($id);
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Author not found'], 404);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'An error occurred while deleting the author data'], 500);
        }
    }

    /**
     * Clear author-related cache
     *
     * @param int|null $authorId
     * @return void
     */
    private function clearAuthorCache($authorId = null)
    {
        Cache::forget('authors.all');
        if ($authorId) Cache::forget("author.{$authorId}");
    }
}
