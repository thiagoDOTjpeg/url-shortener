<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UrlController;

Route::post("/shorten", [UrlController::class, "store"]);
