<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\AuthorController;

Route::apiResource('authors', AuthorController::class);
Route::apiResource('books', BookController::class);
Route::get('authors/{id}/books', [BookController::class, 'booksByAuthor']);