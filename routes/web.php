<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RegisController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::get('/home', function(){
    return view('pages.homepage');
})->name('home');
