<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UrlController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AnalyticsController;


Route::get('/', function () {
    return view('app');
});

Route::middleware('auth')->get('/dashboard', function () {
    return redirect()->route('dashboard.home');
});

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/dashboard/home');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login.form');

    Route::get('/register', function () {
        return view('auth.register');
    })->name('register.form');

    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

Route::get('/r/{slug}', RedirectController::class)->name('url.redirect');


Route::middleware(['auth', 'verified'])->prefix('dashboard')->group(function () {

    Route::get('/home', [DashboardController::class, 'home'])->name('dashboard.home');

    Route::get('/analytics/{slug}', [AnalyticsController::class, 'show'])
        ->name('dashboard.analytics');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::post('/urls/shorten', [UrlController::class, 'store'])->name('url.store');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::delete("/urls/{slug}", [UrlController::class, 'destroy'])->name('url.destroy');
});

