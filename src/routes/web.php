<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SerpController;

Route::match(['get','post'], '/', [SerpController::class, 'index'])->name('home');
