<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UrlController;

Route::get('/', function () {
    return Auth::check() ? redirect()->route("dashboard.home") : view('app');
});

Route::get('/dashboard', function () {
    return Auth::check() ? redirect()->route('dashboard.home') : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');

    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

Route::get('/r/{slug}', [UrlController::class, 'show'])->name('url.redirect');


Route::middleware('auth')->group(function () {
    Route::get('/dashboard/home', [UrlController::class, 'index'])->name('dashboard.home');

    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::post('/shorten', [UrlController::class, 'store'])->name('url.store');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get("/urls", [UrlController::class, 'index'])->name('url.index');
});

