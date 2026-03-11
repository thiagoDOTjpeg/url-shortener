<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\AuthController;

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

    Route::post('register', [AuthController::class, 'register'])->name('register');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard/home', function () {
        return view('dashboard.home');
    })->name('dashboard.home');
});
