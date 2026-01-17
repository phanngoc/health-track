<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Check-in routes
    Route::get('/checkins', [\App\Http\Controllers\CheckInController::class, 'index'])->name('checkins.index');
    Route::get('/checkins/create', [\App\Http\Controllers\CheckInController::class, 'create'])->name('checkins.create');
    Route::post('/checkins', [\App\Http\Controllers\CheckInController::class, 'store'])->name('checkins.store');

    // Timeline route
    Route::get('/timeline', [\App\Http\Controllers\TimelineController::class, 'index'])->name('timeline.index');
});
