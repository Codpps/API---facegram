<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\FollowerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::resource('v1/users', UserController::class);
Route::get('/alluser', [UserController::class, 'allUser']);
Route::post('/register/post', [AuthController::class, 'registerpost'])->name('register.post');
Route::post('/login/post', [AuthController::class, 'loginpost'])->name('login.post');
Route::get('/posts', [PostController::class, 'index'])->name('posts');
Route::post('/post/store', [PostController::class, 'store'])->middleware('auth:sanctum')->name('post.store');
Route::delete('/post/destroy/{id}', [PostController::class, 'destroy'])->name('post.destroy');
Route::get('/posts', [PostController::class, 'index']);
Route::post('/users/{username}/follow', [FollowerController::class, 'follow'])->middleware('auth:sanctum');
Route::get('/users/{username}/followers', [FollowerController::class, 'followers'])->middleware('auth:sanctum');
Route::put('/users/{username}/accept-follow', [FollowerController::class, 'acceptFollow'])->middleware('auth:sanctum');
Route::delete('/users/{username}/unfollow', [FollowerController::class, 'unfollow'])->middleware('auth:sanctum');
Route::get('/users', [UserController::class, 'getUnfollowedUsers'])->middleware('auth:sanctum');
Route::get('/users/{username}', [UserController::class, 'getUserDetail'])->middleware('auth:sanctum');
// Tambahkan middleware auth:sanctum untuk route logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum')
    ->name('logout');
