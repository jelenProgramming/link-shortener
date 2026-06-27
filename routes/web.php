<?php

use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'name' => 'Linkshort API',
    'status' => 'ok',
    'docs' => '/api/links',
]));

// Short link redirect. Kept last so it doesn't shadow other routes.
Route::get('/{slug}', RedirectController::class)->where('slug', '[A-Za-z0-9_-]+');
