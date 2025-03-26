<?php

use App\Models\Post;
use App\Models\User;
use App\Models\Asset;
use App\Models\Interaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\JobRequestController;

Route::controller(AuthController::class)->group(function () {
    Route::post('check', 'checkStatus');
    Route::post('check-duplicate', 'checkDuplicate');
    Route::post('register', 'register');
    Route::post('login', 'login');
});
Route::get('/trending-tags', [PostController::class, 'getTrendingTags']);

Route::get('/posts/following', [PostController::class, 'getFollowingPosts']);
Route::get('/posts/trending', [PostController::class, 'getTrendingPosts']);
Route::get('/get-posts', [PostController::class, 'getAllPosts']);
Route::get('/get-post/{id}', [PostController::class, 'getPost']);
Route::get('/search/{value?}', [PostController::class, 'search']);
Route::get('/user/{name}', [UserController::class, 'getUserAndPosts']);
Route::get('/job-requests', [JobRequestController::class, 'index']);
Route::get('/job-requests/{id}', [JobRequestController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json([
            'user' => $request->user()->load('profile.profileImage')
        ]);
    });
    Route::post('/user/follow', [UserController::class, 'toggleFollow']);
    Route::get('/notifications', [UserController::class, 'getNotifications']);
    Route::delete('/notifications/{id}', [UserController::class, 'deleteNotification']);

    Route::post('/user/update', [UserController::class, 'updateUser']);
    Route::get('/bookmarked-posts', [UserController::class, 'userBookmarks']);
    Route::post('/post/save', [UserController::class, 'savePost']);
    Route::post('/add-post/like', [UserController::class, 'addLike']);
    Route::post('/add-post/comment', [UserController::class, 'addComment']);
    Route::post('/add-post/share', [UserController::class, 'sharePost']);
    Route::get('/get-comments', [PostController::class, 'getPostComments']);

    Route::post('/add-post', [PostController::class, 'createPost']);
    Route::put('/edit-post/{post}', [PostController::class, 'editPost']);
    Route::delete('/delete-post/{post}', [PostController::class, 'deletePost']);

    Route::get('/my-job-requests', [JobRequestController::class, 'getJobRequests']);
    Route::post('/job-requests', [JobRequestController::class, 'store']);
    Route::put('/job-requests/{jobRequest}', [JobRequestController::class, 'update']);
    Route::delete('/job-requests/{jobRequest}', [JobRequestController::class, 'destroy']);

    Route::post('/job-requests/{id}/apply', [JobRequestController::class, 'apply']);
    Route::post('/job-requests/{jobRequestId}/accept/{interactionId}', [JobRequestController::class, 'acceptApplicant']);
    Route::post('/job-requests/{jobRequestId}/reject/{interactionId}', [JobRequestController::class, 'rejectApplicant']);
});
