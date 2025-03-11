<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;

Route::controller(AuthController::class)->group(function () {
    Route::post('check', 'checkStatus');
    Route::post('check-duplicate', 'checkDuplicate');
    Route::post('register', 'register');
    Route::post('login', 'login');
});
Route::get('/get-posts', [PostController::class, 'getAllPosts']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/user/bookmakred/{$postId}', [UserController::class, 'userBookmarks']);
    Route::put('/user/update', [UserController::class, 'updateUser']);
    Route::post('/add-post/comment', [UserController::class, 'addComment']);
    Route::post('/add-post/share', [UserController::class, 'sharePost']);
    Route::post('/post/add-save', [UserController::class, 'savePost']);

    Route::post('/add-post', [PostController::class, 'createPost']);
    Route::put('/edit-post/{post}', [PostController::class, 'editPost']);
    Route::delete('/delete-post/{post}', [PostController::class, 'deletePost']);
});
