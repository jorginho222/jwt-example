<?php

use Illuminate\Support\Facades\Route;

Route::post('/login', [\App\Http\Controllers\LoginController::class, 'login']);
Route::post('/user', [\App\Http\Controllers\RegisterController::class, 'store']);
