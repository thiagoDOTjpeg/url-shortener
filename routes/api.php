<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UrlController;
use App\Http\Controllers\AuthController;


Route::middleware("auth:sanctum")->group(function () {
    Route::get("/me", [AuthController::class, "me"]);
    Route::post("/shorten", [UrlController::class, "store"]);
});

Route::get("/{slug}", [UrlController::class, "show"]);
