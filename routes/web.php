<?php

use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'name' => 'Shortify API',
    'status' => 'ok',
    'docs' => '/api/links',
]));

// must stay last
Route::get('/{slug}', RedirectController::class)->where('slug', '[A-Za-z0-9_-]+');
