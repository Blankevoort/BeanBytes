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
Route::get('/trending-tags', [PostController::class, 'getTrendingTags']);

Route::get('/get-posts', [PostController::class, 'getAllPosts']);
Route::get('/get-post/{id}', [PostController::class, 'getPost']);
Route::get('/search/{value?}', [PostController::class, 'search']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/user/follow', [UserController::class, 'toggleFollow']);
    Route::get('/notifications', [UserController::class, 'getNotifications']);
    Route::delete('/notifications/{id}', [UserController::class, 'deleteNotification']);

    Route::get('/user/bookmarks', [UserController::class, 'userBookmarks']);
    Route::put('/user/update', [UserController::class, 'updateUser']);
    Route::post('/post/save', [UserController::class, 'savePost']);
    Route::post('/add-post/like', [UserController::class, 'addLike']);
    Route::post('/add-post/comment', [UserController::class, 'addComment']);
    Route::post('/add-post/share', [UserController::class, 'sharePost']);
    Route::get('/get-comments', [PostController::class, 'getPostComments']);

    Route::post('/add-post', [PostController::class, 'createPost']);
    Route::put('/edit-post/{post}', [PostController::class, 'editPost']);
    Route::delete('/delete-post/{post}', [PostController::class, 'deletePost']);
});
