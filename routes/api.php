<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LinkController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::post('/links', [LinkController::class, 'store'])->middleware('throttle:30,1');
Route::get('/links/{slug}', [LinkController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/links', [LinkController::class, 'index']);
    Route::delete('/links/{slug}', [LinkController::class, 'destroy']);
});
