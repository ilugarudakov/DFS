<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SerpController;

Route::get('/', [SerpController::class, 'show'])->name('home');
Route::post('/search', [SerpController::class, 'search'])->name('search');
